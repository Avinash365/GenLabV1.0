<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\NewBooking;
use App\Models\Invoice;

use App\Services\GetUserActiveDepartment; 

class MarketingPersonLedger extends Controller
{
     
    protected $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index(Request $request)
    {
        $search       = $request->input('search');
        $filterPerson = $request->input('person_id');
        $month        = $request->input('month');
        $year         = $request->input('year');

        // Fetch marketing persons
        $marketingPersons = User::whereHas('role', function ($q) {
                $q->where('slug', 'marketing_person');
            })
            ->when($filterPerson, function ($query) use ($filterPerson) {
                $query->where('id', $filterPerson);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('user_code', 'like', "%{$search}%");
                });
            })
            ->with([
                'marketingBookings' => function ($q) use ($month, $year) {
                    if ($month) {
                        $q->whereMonth('created_at', $month);
                    }
                    if ($year) {
                        $q->whereYear('created_at', $year);
                    }
                    $q->with(['items', 'generatedInvoice']);
                }
            ])
            ->paginate(10);

        // Ledger data
        $ledgerData = $marketingPersons->map(function ($person) {
            $bookings = $person->marketingBookings;

            $totalBookingCount = $bookings->count();

            $totalBookingAmount = $bookings->flatMap(function ($b) {
                return $b->items;
            })->sum(fn($item) => $item->amount);

            $totalInvoiceAmount = $bookings->flatMap(function ($b) {
                return $b->generatedInvoice ? [$b->generatedInvoice] : [];
            })->sum('total_amount');

            $paidInvoiceAmount = $bookings->flatMap(function ($b) {
                return $b->generatedInvoice && $b->generatedInvoice->status
                    ? [$b->generatedInvoice]
                    : [];
            })->sum('total_amount');

            $balance = $totalInvoiceAmount - $paidInvoiceAmount;

            $bookingRefs = $bookings->pluck('reference_no')->toArray();

            

            return [
                'person'               => $person,
                'total_bookings'       => $totalBookingCount,
                'total_booking_amount' => $totalBookingAmount,
                'total_invoice_amount' => $totalInvoiceAmount,
                'paid_amount'          => $paidInvoiceAmount,
                'balance'              => $balance,
                'booking_refs'         => $bookingRefs,
            ];
        });

        // Totals
        $totals = [
            'total_booking_amount' => $ledgerData->sum('total_booking_amount'),
            'total_invoice_amount' => $ledgerData->sum('total_invoice_amount'),
            'paid_amount'          => $ledgerData->sum('paid_amount'),
            'balance'              => $ledgerData->sum('balance'),
        ];
        $departments = $this->departmentService->getDepartment(); 
        return view('superadmin.accounts.marketingPerson.index', compact(
            'marketingPersons',
            'ledgerData',
            'search',
            'totals',
            'filterPerson',
            'month',
            'year', 
            'departments'
        ));
    }  

    public function show($userCode)
    {
        try {
            // Find Marketing Person
            $marketingPerson = User::where('user_code', $userCode)->firstOrFail();

            // All bookings by this marketing person
            $bookings = NewBooking::with('items', 'generatedInvoice')
                ->where('marketing_id', $userCode)
                ->get();

            // All invoices linked with those bookings
            $invoices = Invoice::whereIn('new_booking_id', $bookings->pluck('id'))->get();

            // Calculations
            $totalBookings = $bookings->count();
            $totalWithoutBillBookings = $bookings->where('payment_option', 'without_bill')->count();
            $totalBookingAmount = $bookings->sum(fn ($b) => $b->total_amount);
            $totalInvoiceAmount = $invoices->sum('total_amount');
            $totalGeneratedInvoices = $invoices->count();
            $paidAmount = $invoices->where('status', 1)->sum('total_amount');
            $balance = $totalInvoiceAmount - $paidAmount;

            return view('superadmin.accounts.marketingPerson.profile', [
                'marketingPerson' => $marketingPerson,
                'bookings' => $bookings,
                'invoices' => $invoices,
                'stats' => [
                    'totalBookings' => $totalBookings,
                    'totalWithoutBillBookings' => $totalWithoutBillBookings,
                    'totalBookingAmount' => $totalBookingAmount,
                    'totalInvoiceAmount' => $totalInvoiceAmount,
                    'totalGeneratedInvoices' => $totalGeneratedInvoices,
                    'paidAmount' => $paidAmount,
                    'balance' => $balance,
                ],
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
