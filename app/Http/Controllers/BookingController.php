<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreBookingRequest;
use App\Models\NewBooking;
use App\Models\BookingItem;
use App\Services\JobOrderService;
use App\Models\User;
use App\Enums\Role;

class BookingController extends Controller
{
    public function __construct()
    {
        // This will automatically check your BookingPolicy for all resource methods
        $this->authorizeResource(NewBooking::class, 'new_booking');
    }

    /**
     * Show booking list
     */
    public function index()
    {
        $bookings = NewBooking::with('items')->latest()->paginate(10);

        return view('superadmin.Bookings.index', compact('bookings'));
    }

    /**
     * Show booking create form
     */
    public function create()
    {
        return view('superadmin.Bookings.newBooking');
    }

    /**
     * Store a new booking
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            // Determine creator dynamically
            if (auth('admin')->check()) {
                $creatorId = auth('admin')->id();
                $creatorType = 'App\\Models\\Admin';
            } elseif (auth('web')->check()) {
                $creatorId = auth('web')->id();
                $creatorType = 'App\\Models\\User';
            } else {
                abort(403, 'Unauthorized');
            }

            DB::transaction(function () use ($request, $creatorId, $creatorType) {
                $bookingData = $request->only([
                    'client_name',
                    'client_address',
                    'job_order_date',
                    'report_issue_to',
                    'reference_no',
                    'marketing_id',
                    'contact_no',
                    'contact_email',
                    'hold_status',
                ]);

                $bookingData['created_by_id']   = $creatorId;
                $bookingData['created_by_type'] = $creatorType;

                $booking = NewBooking::create($bookingData);

                if ($request->has('booking_items')) {
                    foreach ($request->booking_items as $item) {
                        $deptCode = $item['job_order_no'] ?? 'GEN';

                        $item['job_order_no'] = JobOrderService::generateJobOrderNo($deptCode);

                        $booking->items()->create($item);
                    }
                }
            });

            return redirect()
                ->route('superadmin.bookings.index')
                ->with('success', 'Booking created successfully!');

        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * Update an existing booking
     */
    public function update(StoreBookingRequest $request, NewBooking $new_booking)
    {
        try {
            DB::transaction(function () use ($request, $new_booking) {
                if (auth('admin')->check()) {
                    $creatorId   = auth('admin')->id();
                    $creatorType = 'App\\Models\\Admin';
                } elseif (auth('web')->check()) {
                    $creatorId   = auth('web')->id();
                    $creatorType = 'App\\Models\\User';
                } else {
                    abort(403, 'Unauthorized');
                }

                $bookingData = $request->only([
                    'client_name',
                    'client_address',
                    'job_order_date',
                    'report_issue_to',
                    'reference_no',
                    'marketing_id',
                    'contact_no',
                    'contact_email',
                    'hold_status',
                ]);

                $bookingData['created_by_id']   = $creatorId;
                $bookingData['created_by_type'] = $creatorType;

                $new_booking->update($bookingData);

                if ($request->has('booking_items')) {
                    foreach ($request->booking_items as $key => $item) {
                        if (is_numeric($key)) {
                            $bookingItem = $new_booking->items()->find($key);
                            if ($bookingItem) {
                                $bookingItem->update($item);
                            }
                        } else {
                            $new_booking->items()->create($item);
                        }
                    }
                }
            });

            return redirect()
                ->route('superadmin.bookings.index')
                ->with('success', 'Booking updated successfully!');

        } catch (\Exception $e) {
            Log::error('Booking update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * Delete a booking
     */
    public function destroy(NewBooking $new_booking)
    {
        try {
            DB::transaction(function () use ($new_booking) {
                $new_booking->items()->delete();
                $new_booking->delete();
            });

            return redirect()
                ->route('superadmin.bookings.index')
                ->with('success', 'Booking deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Booking deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * Autocomplete job orders
     */
    public function getJobOrders(Request $request)
    {
        $search = $request->query('term');

        $results = BookingItem::where('job_order_no', 'LIKE', "%{$search}%")
            ->distinct()
            ->pluck('job_order_no');

        return response()->json($results);
    }

    /**
     * Autocomplete Lab Analyst users
     */
    public function getLabAnalyst(Request $request)
    {
        $query = $request->query('term');

        $results = User::whereHas('role', function ($q) {
                $q->where('slug', Role::LAB_ANALYST->value);
            })
            ->when($query, function ($q) use ($query) {
                $q->where('user_code', 'like', '%' . $query . '%');
            })
            ->pluck('user_code');

        return response()->json($results);
    }

    /**
     * Autocomplete Marketing Person users
     */
    public function getMarketingPerson(Request $request)
    {
        $query = $request->query('term');

        $results = User::whereHas('role', function ($q) {
                $q->where('slug', Role::MARKETING_PERSON->value);
            })
            ->when($query, function ($q) use ($query) {
                $q->where('user_code', 'like', '%' . $query . '%');
            })
            ->pluck('user_code');

        return response()->json($results);
    }
}
