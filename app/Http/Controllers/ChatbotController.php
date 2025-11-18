<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// Simple PHP-based NLP keyword extraction
function extract_keywords($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9 ]/', '', $text);
    $stopwords = ['the','is','at','which','on','a','an','and','or','of','to','in','for','with','by','as','from','that','this','it','be','are','was','were','has','have','had','do','does','did','but','if','so','can','will','just','about','into','than','then','too','very','more','also','any','all','such','no','not','only','own','same','some','other','over','out','up','down','off','again','further','once'];
    $words = array_filter(explode(' ', $text), function($w) use ($stopwords) {
        return $w && !in_array($w, $stopwords);
    });
    return array_values($words);
}

class ChatbotController extends Controller
{
    protected function resolveCurrentUserName(Request $request): ?string
    {
        $guards = ['web', 'admin'];
        if ($request->user()) {
            return $request->user()->name ?? null;
        }
        foreach ($guards as $guard) {
            $user = Auth::guard($guard)->user();
            if ($user && !empty($user->name)) {
                return $user->name;
            }
        }
        return null;
    }

    // Helper: parse timeframe words -> date range
    protected function parseTimeframe(string $text): array
    {
        $today = now()->startOfDay();
        $endToday = now()->endOfDay();
        $lower = strtolower($text);
        if (preg_match('/today/', $lower)) {
            return [$today, $endToday, 'today'];
        }
        if (preg_match('/this\s+week|current\s+week/', $lower)) {
            return [now()->startOfWeek(), now()->endOfWeek(), 'this week'];
        }
        if (preg_match('/this\s+month|current\s+month/', $lower)) {
            return [now()->startOfMonth(), now()->endOfMonth(), 'this month'];
        }
        if (preg_match('/this\s+year|current\s+year/', $lower)) {
            return [now()->startOfYear(), now()->endOfYear(), 'this year'];
        }
        // Default: all time
        return [null, null, 'all time'];
    }

    protected function htmlList($label, $items, $limit = null)
    {
        if ($items->isEmpty()) return 'No data found for ' . e($label) . '.';
        if ($limit && $items->count() > $limit) {
            $shown = $items->take($limit)->map(fn($v)=>e($v))->implode('<br>');
            return e($label) . ':<br>' . $shown . '<br><em>+' . ($items->count()-$limit) . ' more</em>';
        }
        return e($label) . ':<br>' . $items->map(fn($v)=>e($v))->implode('<br>');
    }

    public function query(Request $request)
    {
        $question = $request->input('question', '');
        Log::info('Chatbot received question: ' . $question);
        $keywords = extract_keywords($question);
        $lowerQ = strtolower($question);
        $userName = $this->resolveCurrentUserName($request);

        if (preg_match('/^\s*(hi|hello)(\b|!|\?)/i', $question)) {
            $personalized = $userName ? 'Welcome ' . e($userName) . '! How can I help you today?' : 'Welcome! How can I help you today?';
            return response()->json(['answer' => $personalized]);
        }

        // 1. Invoices today (count & sum)
        if (preg_match('/invoice.*today|today.*invoice/', $lowerQ)) {
            $count = DB::table('invoices')->whereDate('invoice_date', now()->toDateString())->count();
            $sum = DB::table('invoices')->whereDate('invoice_date', now()->toDateString())->sum('total_amount');
            return response()->json(['answer' => 'Invoices today: ' . $count . ' | Total amount: ' . number_format($sum,2)]);
        }

        // 1b. Total amount of invoices (all time or timeframe)
        if (preg_match('/total\s+amount.*invoice|invoice.*total\s+amount|sum.*invoice|invoice.*sum/', $lowerQ)) {
            [$start,$end,$label] = $this->parseTimeframe($lowerQ);
            $query = DB::table('invoices');
            if ($start) { $query->whereBetween('invoice_date', [$start, $end]); }
            $sum = $query->sum('total_amount');
            return response()->json(['answer' => 'Total invoice amount ' . $label . ': ' . number_format($sum,2)]);
        }

        // 2. Bookings count (optionally timeframe + department)
        if (preg_match('/how\s+many.*booking/', $lowerQ)) {
            [$start,$end,$label] = $this->parseTimeframe($lowerQ);
            $query = DB::table('new_bookings');
            // department filter
            if (preg_match('/department\s+([a-z0-9 \-_]+)/', $lowerQ, $m)) {
                $deptName = trim($m[1]);
                $deptId = DB::table('departments')->where('name','like','%'.$deptName.'%')->value('id');
                if ($deptId) $query->where('department_id',$deptId);
            }
            if ($start) { $query->whereBetween('created_at', [$start, $end]); }
            $count = $query->count();
            return response()->json(['answer' => 'Bookings ' . $label . ': ' . $count]);
        }

        // 3. Bookings by client (timeframe) e.g. "bookings of ACME this month"
        if (preg_match('/booking[s]?\s+.*(client|of)\s+([a-z0-9 \-]+)/', $lowerQ, $m)) {
            $clientFragment = trim($m[2]);
            [$start,$end,$label] = $this->parseTimeframe($lowerQ);
            $query = DB::table('new_bookings')->where('client_name','like','%'.$clientFragment.'%');
            if ($start) $query->whereBetween('created_at', [$start,$end]);
            $count = $query->count();
            return response()->json(['answer' => 'Bookings for client ('.$clientFragment.') '.$label.': '.$count]);
        }

        // 4. Recent bookings of marketing person (assumes new_bookings.marketing_id -> users.id and role_name contains marketing)
        if (preg_match('/recent.*booking.*(marketing|marketer)\s+([a-z0-9 \-]+)/', $lowerQ, $m)) {
            $person = trim($m[2]);
            $userId = DB::table('users')
                ->join('roles','users.role_id','=','roles.id')
                ->where('roles.role_name','like','%market%')
                ->where('users.name','like','%'.$person.'%')
                ->value('users.id');
            if (!$userId) return response()->json(['answer'=>'No marketing person matched: '.$person]);
            $rows = DB::table('new_bookings')
                ->where('marketing_id',$userId)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id','client_name','created_at']);
            if ($rows->isEmpty()) return response()->json(['answer'=>'No recent bookings for '.$person]);
            $list = $rows->map(fn($r)=>'#'.$r->id.' '.$r->client_name.' ('.\Carbon\Carbon::parse($r->created_at)->format('d M').')')->implode('<br>');
            return response()->json(['answer'=>'Recent bookings for '.$person.':<br>'.$list]);
        }

        // 5. Job order status (assumes reference_no column)
        if (preg_match('/status.*job\s*order.*(\d+)/', $lowerQ, $m)) {
            $ref = $m[1];
            $row = DB::table('new_bookings')->where('reference_no','like','%'.$ref.'%')->first();
            if (!$row) return response()->json(['answer'=>'No job order found matching '.$ref]);
            // Placeholder: no explicit status column; using hold_status or created_at
            $status = property_exists($row,'hold_status') ? ($row->hold_status ?: 'N/A') : 'N/A';
            return response()->json(['answer'=>'Job order '.$ref.' status: '.$status]);
        }

        // 6. When job order booked
        if (preg_match('/when.*job\s*order.*(\d+)/', $lowerQ, $m)) {
            $ref = $m[1];
            $row = DB::table('new_bookings')->where('reference_no','like','%'.$ref.'%')->first();
            if (!$row) return response()->json(['answer'=>'No job order found matching '.$ref]);
            $date = \Carbon\Carbon::parse($row->created_at)->toDayDateTimeString();
            return response()->json(['answer'=>'Job order '.$ref.' booked on '.$date]);
        }

        // 7. Open my profile (link)
        if (preg_match('/open.*my.*profile|my\s+profile/', $lowerQ)) {
            return response()->json(['answer'=>'Profile: <a href="'.url('/superadmin/profile').'" target="_blank">Open Profile</a>']);
        }

        // 8. TODO intents requiring schema clarification
        $todoPatterns = [
            'ledger' => 'Ledger feature not implemented. Provide table & column details.',
            'expense' => 'Expenses feature not implemented. Provide table & column details.',
            'payment due' => 'Payment due feature not implemented. Provide table & column details.',
            'attendance' => 'Attendance feature not implemented. Provide table & column details.',
            'expected date' => 'Lab expected date feature not implemented. Provide table & column details.',
            'work done' => 'Work done tracking not implemented. Provide table & column details.'
        ];
        foreach ($todoPatterns as $phrase=>$msg) {
            if (str_contains($lowerQ,$phrase)) {
                return response()->json(['answer'=>$msg]);
            }
        }

        // === Existing generic intent + fallback logic below ===
        // Improved intent mapping: allow plural/synonyms
        $intentMap = [
            'marketing' => ['table' => 'users', 'role' => 'marketing', 'type' => 'role_list', 'label' => 'Marketing Persons'],
            'marketer' => ['table' => 'users', 'role' => 'marketing', 'type' => 'role_list', 'label' => 'Marketing Persons'],
            'lab' => ['table' => 'users', 'role' => 'lab analyst', 'type' => 'role_list', 'label' => 'Lab Analysts'],
            'analyst' => ['table' => 'users', 'role' => 'lab analyst', 'type' => 'role_list', 'label' => 'Lab Analysts'],
            'analysts' => ['table' => 'users', 'role' => 'lab analyst', 'type' => 'role_list', 'label' => 'Lab Analysts'],
            'user' => ['table' => 'users', 'column' => 'code', 'type' => 'list', 'label' => 'User Codes'],
            'users' => ['table' => 'users', 'column' => 'code', 'type' => 'list', 'label' => 'User Codes'],
            'department' => ['table' => 'departments', 'column' => 'name', 'type' => 'list', 'label' => 'Departments'],
            'departments' => ['table' => 'departments', 'column' => 'name', 'type' => 'list', 'label' => 'Departments'],
            'booking' => ['table' => 'new_bookings', 'column' => 'id', 'type' => 'count', 'label' => 'Total Bookings'],
            'bookings' => ['table' => 'new_bookings', 'column' => 'id', 'type' => 'count', 'label' => 'Total Bookings'],
            'item' => ['table' => 'booking_items', 'column' => 'id', 'type' => 'count', 'label' => 'Total Booking Items'],
            'items' => ['table' => 'booking_items', 'column' => 'id', 'type' => 'count', 'label' => 'Total Booking Items'],
            'product' => ['table' => 'products', 'column' => 'product_name', 'type' => 'list', 'label' => 'Products'],
            'products' => ['table' => 'products', 'column' => 'product_name', 'type' => 'list', 'label' => 'Products'],
            'invoice' => ['table' => 'invoices', 'column' => 'id', 'type' => 'count', 'label' => 'Total Invoices'],
            'invoices' => ['table' => 'invoices', 'column' => 'id', 'type' => 'count', 'label' => 'Total Invoices'],
            'quotation' => ['table' => 'quotations', 'column' => 'id', 'type' => 'count', 'label' => 'Total Quotations'],
            'quotations' => ['table' => 'quotations', 'column' => 'id', 'type' => 'count', 'label' => 'Total Quotations'],
        ];
        $foundIntent = null;
        foreach ($keywords as $kw) {
            if (isset($intentMap[$kw])) {
                $foundIntent = $intentMap[$kw];
                break;
            }
        }

        if ($foundIntent) {
            if ($foundIntent['type'] === 'role_list') {
                // Join users and roles to get names by role_name
                $results = DB::table('users')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->where('roles.role_name', 'like', '%' . $foundIntent['role'] . '%')
                    ->pluck('users.name');
                if ($results->count() > 0) {
                    $answer = $foundIntent['label'] . ':<br>' . $results->implode('<br>');
                } else {
                    $answer = 'No data found for ' . $foundIntent['label'] . '.';
                }
            } elseif ($foundIntent['type'] === 'list') {
                $results = DB::table($foundIntent['table'])->pluck($foundIntent['column']);
                if ($results->count() > 0) {
                    $answer = $foundIntent['label'] . ':<br>' . $results->implode('<br>');
                } else {
                    $answer = 'No data found for ' . $foundIntent['label'] . '.';
                }
            } elseif ($foundIntent['type'] === 'count') {
                $count = DB::table($foundIntent['table'])->count();
                $answer = $foundIntent['label'] . ': ' . $count;
            } else {
                $answer = 'Sorry, I could not process your request.';
            }
        } else {
            // Fallback: fuzzy search all tables for keywords
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_laravel';
            $responses = [];
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
                $stringColumns = [];
                foreach ($columns as $col) {
                    $type = DB::getSchemaBuilder()->getColumnType($tableName, $col);
                    if (in_array($type, ['string', 'text'])) {
                        $stringColumns[] = $col;
                    }
                }
                if (count($stringColumns) === 0) continue;
                $query = DB::table($tableName);
                $query->select($stringColumns);
                $query->where(function($q) use ($stringColumns, $keywords) {
                    foreach ($keywords as $kw) {
                        foreach ($stringColumns as $col) {
                            $q->orWhere($col, 'LIKE', "%$kw%");
                        }
                    }
                });
                $results = $query->limit(3)->get();
                if (count($results) > 0) {
                    foreach ($results as $row) {
                        $responses[] = '<b>' . ucfirst($tableName) . '</b>: ' . implode(', ', array_map('htmlspecialchars', (array)$row));
                    }
                }
            }
            if (count($responses) > 0) {
                $answer = "Here's what I found:<br>" . implode('<br><br>', $responses);
            } else {
                $answer = "Sorry, I couldn't find that information. Please contact support.";
            }
        }
        Log::info('Chatbot response: ' . $answer);
        return response()->json(['answer' => $answer]);
    }
}
