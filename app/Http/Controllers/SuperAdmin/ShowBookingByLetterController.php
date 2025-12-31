<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingItem;
use App\Services\GetUserActiveDepartment;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingItemsExport;

class ShowBookingByLetterController extends Controller
{
    protected $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;

    }

    public function index(Request $request)
    {
        $query = $this->buildQuery($request);

        // Get results (paginated)
        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100,500])) { $perPage = 25; }
        $items = $query->latest()->paginate($perPage)->withQueryString();

        // Return view
        return view('superadmin.showbooking.bookingByLetter', [
            'items' => $items,
            'search' => $request->input('search'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ]);
    }   

    public function marketingIndex(Request $request)
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();

        // Ensure marketing param sticks to request for pagination/export when marketing user
        if ($this->isMarketingUser($user) && !$request->filled('marketing') && !empty($user->user_code)) {
            $request->merge(['marketing' => $user->user_code]);
        }

        $query = $this->buildQuery($request);

        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100])) { $perPage = 25; }
        $items = $query->latest()->paginate($perPage)->withQueryString();

        return view('superadmin.showbooking.marketing.bookingByLetter', [
            'items' => $items,
            'search' => $request->input('search'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'marketing' => $request->input('marketing'),
        ]);
    }

    public function destroy(BookingItem $bookingItem)
    {
        $bookingItem->delete();

        return redirect()->back()
                        ->with('success', 'Booking item deleted successfully.');
    }

    protected function buildQuery(Request $request)
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $search = $request->input('search');
        $month  = $request->input('month');
        $year   = $request->input('year');
        $marketingFilter = $request->input('marketing');

        // Auto-enforce marketing scoping for marketing users
        if ($this->isMarketingUser($user)) {
            $marketingFilter = $marketingFilter ?: ($user->user_code ?? null);
        }

        $query = BookingItem::with(['booking', 'booking.marketingPerson']);

        if (!empty($search)) {
            $search = trim($search);

            $query->where(function ($q) use ($search) {
                $q->where('job_order_no', 'like', $search . '%');
            });
        }
        if (!empty($month)) {
            $query->whereMonth('lab_expected_date', $month);
        }

        if (!empty($year)) {
            $query->whereYear('lab_expected_date', $year);
        }

        // If marketing filter is provided (expects user_code), limit to bookings for that marketing person
        if (!empty($marketingFilter)) {
            $query->whereHas('booking', function ($bq) use ($marketingFilter) {
                $bq->where('marketing_id', $marketingFilter);
            });
        }

        return $query;
    }

    /**
     * Determine if the authenticated user is a marketing user.
     */
    private function isMarketingUser($user): bool
    {
        if (!$user) {
            return false;
        }

        $roleName = null;
        if (isset($user->role)) {
            if (is_object($user->role)) {
                $roleName = $user->role->role_name ?? $user->role->name ?? null;
            } else {
                $roleName = $user->role;
            }
        }

        return $roleName && stripos($roleName, 'market') !== false;
    }

    public function exportPdf(Request $request)
    {
        $items = $this->buildQuery($request)->latest()->get();

        $pdf = Pdf::loadView('superadmin.showbooking.bookingByLetter_pdf', [
            'items' => $items,
            'search' => $request->input('search'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('booking_items.pdf');
    }

    public function exportExcel(Request $request)
    {
        $items = $this->buildQuery($request)->latest()->get();
        return Excel::download(new BookingItemsExport($items), 'booking_items.xlsx');
    }
}
