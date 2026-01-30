<?php 
namespace App\Http\Controllers\Accounts; 

use App\Http\Controllers\Controller; 


use Illuminate\Http\Request;
use App\Models\BookingItem;
use App\Models\Department; 
use App\Models\NewBooking; 
use App\Models\Client; 

use App\Services\GetUserActiveDepartment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class AccountsLetterController extends Controller 
{

    protected $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;

    }

    public function index(Request $request)
    {

        $search = $request->input('search');
        $month  = $request->input('month');
        $year   = $request->input('year');
        $departmentId = $request->input('department_id');
        $paymentOption = $request->input('payment_option') ?? "bill";
        $clientId     = $request->input('client_id');
        $marketingPerson = $request->input('marketing_person') ??'';

         $perPage = (int) $request->get('per_page', 25); // default 25

        $perPage = in_array($perPage, [2,10, 25, 50, 100, 500]) ? $perPage : 25; 



        $query = NewBooking::with(['items', 'department', 'marketingPerson']);

        // Department filter
        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
            $department = Department::find($departmentId);
        } else {
            $department = null; 
        }

        if (!empty($clientId)) {
            $query->where('client_id', $clientId);
        }else{
            $query->whereNull('client_id'); 
        }

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "%{$search}%"))
                ->orWhereHas('items', fn($itemQ) => $itemQ->where('lab_analysis_code', 'like', "%{$search}%")
                                                            ->orWhere('job_order_no', 'like', "%{$search}%"))
                ->orWhereHas('marketingPerson', fn($mpQ) => $mpQ->where('name', 'like', "%{$search}%"))
                ->orWhere('job_order_date', 'like', "%{$search}%");
            });
        }

        // Month & Year filter
        if (!empty($month)) {
            $query->whereMonth('job_order_date', $month);
        }

        if (!empty($year)) {
            $query->whereYear('job_order_date', $year);
        }

        // Payment Option filter
        if (!empty($paymentOption)) {
            $query->where('payment_option', $paymentOption); 
        }

        // marketing person filter 
        if(!empty($marketingPerson)) {
            $query->where('marketing_id', $marketingPerson); 
        }

        $bookings = $query->latest()->paginate($perPage);
        $clients = Client::latest()->get(); 
        $departments = $this->departmentService->getDepartment();

        return view('superadmin.accounts.letters.index', compact('bookings', 'department', 'departments', 'search', 'month', 'year', 'clients'));
    }

    public function destroy(BookingItem $bookingItem)
    {
        $bookingItem->delete();

        return redirect()->back()
                        ->with('success', 'Booking item deleted successfully.');
    }

    public function exportPdf(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        // Build same query as index
        $search = $request->input('search');
        $month  = $request->input('month');
        $year   = $request->input('year');
        $departmentId = $request->input('department_id');
        $paymentOption = $request->input('payment_option') ?? "bill";
        $clientId     = $request->input('client_id');
        $marketingPerson = $request->input('marketing_person') ??'';

        $query = NewBooking::with(['items', 'department', 'marketingPerson']);

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
            $department = Department::find($departmentId);
        } else {
            $department = null;
        }

        if (!empty($clientId)) {
            $query->where('client_id', $clientId);
        } else {
            $query->whereNull('client_id');
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "%{$search}%"))
                ->orWhereHas('items', fn($itemQ) => $itemQ->where('lab_analysis_code', 'like', "%{$search}%")
                                                            ->orWhere('job_order_no', 'like', "%{$search}%"))
                ->orWhereHas('marketingPerson', fn($mpQ) => $mpQ->where('name', 'like', "%{$search}%"))
                ->orWhere('job_order_date', 'like', "%{$search}%");
            });
        }

        if (!empty($month)) {
            $query->whereMonth('job_order_date', $month);
        }

        if (!empty($year)) {
            $query->whereYear('job_order_date', $year);
        }

        if (!empty($paymentOption)) {
            $query->where('payment_option', $paymentOption);
        }

        if(!empty($marketingPerson)) {
            $query->where('marketing_id', $marketingPerson);
        }

        $perPage = (int) $request->get('per_page', 25);
        $perPage = in_array($perPage, [2,10, 25, 50, 100, 500]) ? $perPage : 25;

        if ($request->has(['page', 'per_page'])) {
            $bookings = $query->latest()->forPage($request->input('page'), $perPage)->get();
        } else {
            $bookings = $query->latest()->get();
        }

        $pdf = Pdf::loadView('superadmin.accounts.letters.export_pdf', [
            'bookings' => $bookings,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('account_bookings_letters.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Build same query as index (reuse logic)
        $search = $request->input('search');
        $month  = $request->input('month');
        $year   = $request->input('year');
        $departmentId = $request->input('department_id');
        $paymentOption = $request->input('payment_option') ?? "bill";
        $clientId     = $request->input('client_id');
        $marketingPerson = $request->input('marketing_person') ??'';

        $query = NewBooking::with(['items', 'department', 'marketingPerson']);

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        if (!empty($clientId)) {
            $query->where('client_id', $clientId);
        } else {
            $query->whereNull('client_id');
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "%{$search}%"))
                ->orWhereHas('items', fn($itemQ) => $itemQ->where('lab_analysis_code', 'like', "%{$search}%")
                                                            ->orWhere('job_order_no', 'like', "%{$search}%"))
                ->orWhereHas('marketingPerson', fn($mpQ) => $mpQ->where('name', 'like', "%{$search}%"))
                ->orWhere('job_order_date', 'like', "%{$search}%");
            });
        }

        if (!empty($month)) {
            $query->whereMonth('job_order_date', $month);
        }

        if (!empty($year)) {
            $query->whereYear('job_order_date', $year);
        }

        if (!empty($paymentOption)) {
            $query->where('payment_option', $paymentOption);
        }

        if(!empty($marketingPerson)) {
            $query->where('marketing_id', $marketingPerson);
        }

        $perPage = (int) $request->get('per_page', 25);
        $perPage = in_array($perPage, [2,10, 25, 50, 100, 500]) ? $perPage : 25;

        if ($request->has(['page', 'per_page'])) {
            $bookings = $query->latest()->forPage($request->input('page'), $perPage)->get();
        } else {
            $bookings = $query->latest()->get();
        }

        $filename = 'account_bookings_letters_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($bookings) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Client Name','Reference No','Department','Marketing Person','Payment Option','Job Order Date','Items Count']);

            foreach ($bookings as $b) {
                fputcsv($out, [
                    $b->client_name,
                    $b->reference_no,
                    optional($b->department)->name,
                    optional($b->marketingPerson)->name,
                    $b->payment_option,
                    $b->job_order_date ? 
                        \Carbon\Carbon::parse($b->job_order_date)->format('d-m-Y') : '',
                    $b->items->count(),
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}