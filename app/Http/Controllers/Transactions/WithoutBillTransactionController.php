<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithoutBillTransaction;
use App\Models\NewBooking;
use App\Models\{Client, CashLetterPayment}; 

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
        try {
            $validated = $request->validate([
                'client_id'           => 'required|exists:clients,id',
                'marketing_person_id' => 'required|exists:users,user_code',
                'booking_ids'         => 'required|string', // comma-separated
                'total_amount'        => 'required|numeric|min:0',
                'payment_mode'        => 'required|string',
                'transaction_date'    => 'required|date',
                'amount_received'     => 'required|numeric|min:0',
                'notes'               => 'nullable|string',
            ]);

            // Convert booking_ids to array
            $bookingIds = explode(',', $validated['booking_ids']);
            $validated['booking_ids'] = $bookingIds;

            

            // Use transaction to ensure atomicity
            \DB::beginTransaction();

            $cashLetterPayment = CashLetterPayment::create($validated);

            foreach ($bookingIds as $bookingId) {
                \DB::table('cash_letter_payment_bookings')
                    ->updateOrInsert(
                        [
                            'cash_letter_payment_id' => $cashLetterPayment->id,
                            'booking_id'             => $bookingId,
                        ],
                        [
                            'payment_status' => 'paid',
                            'updated_at'     => now(),
                            'created_at'     => now()
                        ]
                    );
            }

            \DB::commit();

            return redirect()->route('superadmin.bookingInvoiceStatuses.index', ['payment_option' => 'without_bill'])
                   ->with('success', 'Cash Letter Payment saved successfully.');
            
        } catch (\Throwable $e) {
            \DB::rollBack();

            // Log the error for debugging
            \Log::error('Cash Letter Payment Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Something went wrong. Please try again later.']);
        }
    }

}
