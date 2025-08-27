<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class HoldCancelController extends Controller
{
    public function index(Request $request)
    {
        $job = trim((string) $request->query('job', ''));

        $items = collect();
        $header = [];

        if ($job !== '') {
            $first = BookingItem::with(['booking', 'receivedBy', 'analyst'])
                ->where('job_order_no', 'like', "%{$job}%")
                ->orderByDesc('id')
                ->first();

            if ($first) {
                $booking = $first->booking;
                $bk = $booking ? $booking->getAttributes() : [];

                // Keys to search for across booking and items (expanded with camelCase and synonyms)
                $workKeys = [
                    'name_of_work','nameOfWork','work_name','workName','nature_of_work','natureOfWork','work','job_work','jobWork',
                    'project_name','projectName','project','project_title','projectTitle','work_title','workTitle',
                    'site','site_name','siteName','site_address','siteAddress','location','address',
                    'job_name','jobName','job_title','jobTitle','work_desc','work_description','workDescription'
                ];
                $issueToKeys = [
                    'issued_to','issuedTo','issue_to','issueTo',
                    'issued_to_name','issuedToName','issue_to_name','issueToName',
                    'issued_to_person','issuedToPerson','issue_to_person','issueToPerson',
                    'issued_to_department','issuedToDepartment','issue_to_department','issueToDepartment',
                    'issued_to_user','issuedToUser','issue_to_user','issueToUser',
                    'issued_to_company','issuedToCompany','issue_to_company','issueToCompany',
                    'issued_to_org','issuedToOrg','issue_to_org','issueToOrg',
                    'department','dept','contact_person','contactPerson','contact_name','contactName'
                ];

                $clientName = $booking->client_name ?? ($bk['clientName'] ?? ($bk['client'] ?? ''));
                $refNo = $booking->reference_no ?? ($bk['referenceNo'] ?? ($bk['ref_no'] ?? ($first->reference_no ?? '')));
                // Prefer the first non-empty across booking, the first item and raw attributes
                $nameOfWork = $this->pickFirstNonEmpty([$booking, $first, $bk], $workKeys);
                $issuedTo = $this->pickFirstNonEmpty([$booking, $first, $bk], $issueToKeys);
                $ms = $booking->ms ?? ($bk['ms_name'] ?? ($bk['contractor'] ?? ($bk['contractor_name'] ?? ($first->ms ?? ''))));

                $jobDateRaw = $booking->job_order_date ?? ($bk['jobDate'] ?? null);
                $issueDateRaw = $booking->issue_date ?? ($bk['issued_date'] ?? ($first->issue_date ?? null));

                $header = [
                    'job_card_no' => $first->job_order_no,
                    'client_name' => $clientName,
                    'job_order_date' => $jobDateRaw ? Carbon::parse($jobDateRaw)->format('Y-m-d') : '',
                    'issue_date' => $issueDateRaw ? Carbon::parse($issueDateRaw)->format('Y-m-d') : '',
                    'reference_no' => $refNo,
                    'sample_description' => $first->sample_description ?? '',
                    'name_of_work' => $nameOfWork,
                    'issued_to' => $issuedTo,
                    'ms' => $ms,
                ];

                $items = BookingItem::with(['booking', 'receivedBy', 'analyst'])
                    ->where('new_booking_id', $first->new_booking_id)
                    ->orderByDesc('id')
                    ->paginate(20)
                    ->appends(['job' => $job]);

                // Fallbacks from items collection if header still missing
                $rows = collect(method_exists($items, 'items') ? $items->items() : $items);
                if (empty($header['name_of_work'])) {
                    $header['name_of_work'] = $rows->map(function($r) use ($workKeys) {
                        foreach ($workKeys as $k) {
                            $v = data_get($r, $k);
                            if (!$v) $v = data_get($r, "booking.$k");
                            $s = trim((string)($v ?? ''));
                            if ($s !== '') return $s;
                        }
                        return null;
                    })->filter()->first() ?? '';
                }
                if (empty($header['issued_to'])) {
                    $header['issued_to'] = $rows->map(function($r) use ($issueToKeys) {
                        foreach ($issueToKeys as $k) {
                            $v = data_get($r, $k);
                            if (!$v) $v = data_get($r, "booking.$k");
                            $s = trim((string)($v ?? ''));
                            if ($s !== '') return $s;
                        }
                        return null;
                    })->filter()->first() ?? '';
                }

                // Last-chance lookups on alternative keys
                if (empty($header['name_of_work'])) {
                    $header['name_of_work'] = $this->pickFirstNonEmpty([$booking, $first, $bk], ['nameOfWork','workName','projectName','projectTitle','workTitle','jobName','jobTitle','workDescription','work_desc']);
                }
                if (empty($header['issued_to'])) {
                    $header['issued_to'] = $this->pickFirstNonEmpty([$booking, $first, $bk], ['issuedTo','issueTo','issuedToName','issueToName','contact_person','contactPerson','contact_name','contactName']);
                }

                // Heuristic final fallback by matching attribute keys
                if (empty($header['name_of_work'])) {
                    $header['name_of_work'] = $this->pickFirstByPattern([$bk, $first, $booking, $rows->first()], [
                        '/name[_]?of[_]?work/i','/work[_]?name/i','/nature[_]?of[_]?work/i','/project/i','/site/i','/job[_]?(name|title)/i','/work(_desc|_description)?/i'
                    ]);
                }
                if (empty($header['issued_to'])) {
                    $header['issued_to'] = $this->pickFirstByPattern([$bk, $first, $booking, $rows->first()], [
                        '/issued[_]?to/i','/issue[_]?to/i','/contact(_person|_name)?/i','/department|dept/i','/to[_]?(name|person|user|company|org)/i'
                    ]);
                }

                if (empty($header['ms'])) {
                    $header['ms'] = $rows->pluck('booking.ms')->filter()->first()
                        ?? $rows->pluck('ms')->filter()->first() ?? '';
                }
                if (empty($header['issue_date'])) {
                    $foundIssue = $rows->pluck('booking.issue_date')->filter()->first()
                        ?? $rows->pluck('issue_date')->filter()->first();
                    if ($foundIssue) $header['issue_date'] = Carbon::parse($foundIssue)->format('Y-m-d');
                }
            }
        }

        return view('superadmin.reporting.hold_cancel', compact('items', 'header', 'job'));
    }

    public function hold(Request $request, $id)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $item = BookingItem::with(['receivedBy'])->findOrFail((int) $id);
        $item->hold_reason = $data['reason'];
        $item->hold_at = now();
        $item->save();

        return response()->json([
            'ok' => true,
            'id' => $item->id,
            'reason' => $item->hold_reason,
            'hold_at' => optional($item->hold_at)->toDateTimeString(),
        ]);
    }

    public function unhold(Request $request, $id)
    {
        $item = BookingItem::with(['receivedBy'])->findOrFail((int) $id);
        $item->hold_reason = null;
        $item->hold_at = null;
        $item->save();

        return response()->json([
            'ok' => true,
            'id' => $item->id,
            'status_text' => $this->statusText($item),
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $item = BookingItem::with(['receivedBy'])->findOrFail((int) $id);
        try {
            $item->hold_reason = null;
            $item->hold_at = null;
            if (Schema::hasColumn('booking_items', 'cancel_reason')) {
                $item->cancel_reason = $data['reason'];
            }
            if (Schema::hasColumn('booking_items', 'cancel_at')) {
                $item->cancel_at = now();
            }
            if (Schema::hasColumn('booking_items', 'is_canceled')) {
                $item->is_canceled = true;
            }
            $item->save();

            return response()->json([
                'ok' => true,
                'id' => $item->id,
                'reason' => $item->cancel_reason ?? $data['reason'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Cancel update failed', ['error' => $e->getMessage()]);
            // Best-effort fallback
            try {
                if (Schema::hasColumn('booking_items', 'is_canceled')) {
                    $item->is_canceled = true;
                } else {
                    $item->hold_reason = 'CANCELED: ' . $data['reason'];
                }
                $item->hold_at = null;
                $item->save();
            } catch (\Throwable $e2) {}
            return response()->json(['ok' => true, 'id' => $item->id, 'reason' => $data['reason']]);
        }
    }

    public function holdAll(Request $request)
    {
        $validated = $request->validate([
            'job' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);
        $job = $validated['job'];
        $reason = $validated['reason'];

        $first = BookingItem::where('job_order_no', 'like', "%{$job}%")->orderByDesc('id')->first();
        if (!$first) {
            return response()->json(['ok' => true, 'updated' => 0]);
        }

        $count = BookingItem::where('new_booking_id', $first->new_booking_id)
            ->update(['hold_reason' => $reason, 'hold_at' => now()]);

        return response()->json(['ok' => true, 'updated' => $count, 'reason' => $reason]);
    }

    public function cancelAll(Request $request)
    {
        $validated = $request->validate([
            'job' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);
        $job = $validated['job'];
        $reason = $validated['reason'];

        $first = BookingItem::where('job_order_no', 'like', "%{$job}%")->orderByDesc('id')->first();
        if (!$first) {
            return response()->json(['ok' => true, 'updated' => 0]);
        }

        try {
            $updates = [];
            if (Schema::hasColumn('booking_items', 'cancel_reason')) { $updates['cancel_reason'] = $reason; }
            if (Schema::hasColumn('booking_items', 'cancel_at')) { $updates['cancel_at'] = now(); }
            if (Schema::hasColumn('booking_items', 'is_canceled')) { $updates['is_canceled'] = true; }
            $updates['hold_reason'] = null; $updates['hold_at'] = null;
            $count = BookingItem::where('new_booking_id', $first->new_booking_id)->update($updates);
            return response()->json(['ok' => true, 'updated' => $count, 'reason' => $reason]);
        } catch (\Throwable $e) {
            Log::warning('CancelAll update failed', ['error' => $e->getMessage()]);
            $count = BookingItem::where('new_booking_id', $first->new_booking_id)
                ->update([Schema::hasColumn('booking_items', 'is_canceled') ? 'is_canceled' : 'hold_reason' => Schema::hasColumn('booking_items', 'is_canceled') ? true : ('CANCELED: ' . $reason), 'hold_at' => null]);
            return response()->json(['ok' => true, 'updated' => $count, 'reason' => $reason]);
        }
    }

    protected function statusText(BookingItem $item): string
    {
        if ($item->received_at) {
            $dt = optional($item->received_at)->format('d M Y, h:i A');
            $name = $item->received_by_name ?? optional($item->receivedBy)->name ?? 'User';
            return 'Received by ' . $name . ($dt ? ' on ' . $dt : '');
        }
        if ($item->analyst) {
            $analyst = $item->analyst; // relation if loaded
            if (is_object($analyst)) {
                $n = $analyst->name ?? '';
                $c = $analyst->user_code ?? '';
                return $n || $c ? ("With Analyst: {$n}" . ($c ? " ({$c})" : '')) : 'With Analyst';
            }
        }
        return 'In Lab / Analyst TBD';
    }

    private function pickFirstNonEmpty($models, array $keys): string
    {
        foreach ((array)$models as $m) {
            if (!$m) continue;
            foreach ($keys as $k) {
                $v = data_get($m, $k);
                if ($v === null) continue;
                $s = trim((string)$v);
                if ($s !== '') return $s;
            }
        }
        return '';
    }

    private function pickFirstByPattern($models, array $patterns): string
    {
        foreach ((array)$models as $m) {
            if (!$m) continue;
            $arr = is_array($m) ? $m : (method_exists($m, 'getAttributes') ? $m->getAttributes() : (is_object($m) && method_exists($m, 'toArray') ? $m->toArray() : []));
            foreach ($arr as $key => $val) {
                if (!is_string($key)) continue;
                foreach ($patterns as $pat) {
                    if (@preg_match($pat, $key)) {
                        $s = trim((string)($val ?? ''));
                        if ($s !== '') return $s;
                    }
                }
            }
        }
        return '';
    }
}
