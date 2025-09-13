<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithoutBillTransaction;
use App\Models\NewBooking;
use App\Models\Client; 

use Carbon\Carbon;


use App\Services\GetUserActiveDepartment;

class WithoutBillTransactionController extends Controller
{
    
    protected $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;
    }
    

    public function index(Request $request)
    {
         return back()->withSuccess('Currently service is not available');
        // Start query with relationships
        $query = NewBooking::with(['items', 'department', 'marketingPerson', 'client'])
            ->whereDoesntHave('generatedInvoice')
            ->where('payment_option', 'without_bill');

        // Filter by client if selected
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhere('contact_no', 'like', "%{$search}%")
                ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "%{$search}%"))
                ->orWhereHas('marketingPerson', fn($mpQ) => $mpQ->where('name', 'like', "%{$search}%"))
                ->orWhereHas('client', fn($clientQ) => $clientQ->where('name', 'like', "%{$search}%"))
                ->orWhereDate('created_at', $search);
            });
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Get all clients for dropdown
        $clients = Client::select('id', 'name')->get(); 

        // Paginate results
        $bookings = $query->latest()->paginate(10)->appends($request->all());

        // Get departments
        $departments = $this->departmentService->getDepartment();

        return view('superadmin.cashPayments.index', compact('bookings', 'departments', 'clients'))
            ->with([
                'search' => $request->search,
                'month' => $request->month,
                'year' => $request->year,
                'client_id' => $request->client_id, // pass selected client
            ]);
    }

    
    public function store(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;

        // total for that month for this client
        $totalAmount = NewBooking::where('client_id', $request->client_id)
                                ->whereMonth('created_at', $month)
                                ->whereYear('created_at', $year)
                                ->get()
                                ->sum(fn ($booking) => $booking->total_amount);

        $paid = $request->amount;
        $due = max(0, $totalAmount - $paid);

        $carryForward = $due; // carry forward due

        $transaction = WithoutBillTransaction::create([
            'client_id'     => $request->client_id ?? "1",
            'total_amount'  => $totalAmount,
            'paid_amount'   => $paid,
            'due_amount'    => $due,
            'carry_forward' => $carryForward,
            'payment_mode'  => $request->payment_mode,
            'reference'     => $request->reference,
            'payment_month' => Carbon::create($year, $month, 1),
        ]);

        // Mark bookings as paid if fully settled
        // if ($due == 0) {
        //     NewBooking::where('client_id', $request->client_id)
        //         ->whereMonth('created_at', $month)
        //         ->whereYear('created_at', $year)
        //         ->update(['status' => 'paid']);
        // }

        return back()->with('success', 'Transaction recorded successfully.');
    }
}
