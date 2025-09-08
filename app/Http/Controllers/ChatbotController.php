<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    public function query(Request $request)
    {
        $question = $request->input('question');
        Log::info('Chatbot received question: ' . $question);
        $keywords = extract_keywords($question);

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
                // Join users and roles to get names by role
                $results = DB::table('users')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->where('roles.name', 'like', '%' . $foundIntent['role'] . '%')
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
