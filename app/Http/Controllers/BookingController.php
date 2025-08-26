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
use App\Models\Department; 
use App\Services\GetUserActiveDepartment;
use App\Services\FileUploadService;
use App\Jobs\GenerateBookingCards;
use App\Services\BookingCardService;


class BookingController extends Controller
{
    protected GetUserActiveDepartment $departmentService;
    protected FileUploadService $fileUploadService;
    protected BookingCardService $bookingCardService; 



    public function __construct(
        GetUserActiveDepartment $departmentService,
        FileUploadService $fileUploadService,  
        BookingCardService $bookingCardService
    ) {
        $this->departmentService = $departmentService;
        $this->fileUploadService = $fileUploadService;
        $this->bookingCardService = $bookingCardService; 
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

    public function edit(NewBooking $new_booking)
    {
        $departments = Department::all();
        return view('superadmin.Bookings.update', [
            'booking' => $new_booking,
            'departments' => $departments
        ]);
    }

    /**
     * Show booking create form
     */
    public function create()
    {
        $departments = $this->departmentService->getDepartment();
        return view('superadmin.Bookings.newBooking', compact('departments'));
    }

   

    public function store(StoreBookingRequest $request)
{   
    // dd($request->all()); 
    // exit; 
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

        $booking = DB::transaction(function () use ($request, $creatorId, $creatorType) {
            
            
            $bookingData = $request->only([
                'client_name',
                'client_address',
                'job_order_date',
                'department_id', 
                'report_issue_to',
                'reference_no',
                'marketing_id',
                'contact_no',
                'contact_email',
                'hold_status',
                'booking_type', 
            ]);

            $bookingData['created_by_id']   = $creatorId;
            $bookingData['created_by_type'] = $creatorType;

            // File upload
            if ($request->hasFile('upload_letter_path')) {
                $bookingData['upload_letter_path'] = $this->fileUploadService->upload(
                    $request->file('upload_letter_path'),
                    'bookings'
                );
            }

            $booking = NewBooking::create($bookingData);

            // Add booking items if present
            if ($request->has('booking_items')) {
                foreach ($request->booking_items as $item) {
                    $booking->items()->create($item);
                }
            }

            return $booking;
        });

        // Dispatch job after successful transaction
        // dispatch(new GenerateBookingCards($booking->id));
        $pdfFileName = $this->bookingCardService->generateCardsForBooking($booking);

          
        return redirect()->away(asset('storage/cards/' . $pdfFileName));
            // ->with('success', 'Booking created successfully!');

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

                // Determine the creator
                if (auth('admin')->check()) {
                    $creatorId   = auth('admin')->id();
                    $creatorType = 'App\\Models\\Admin';
                } elseif (auth('web')->check()) {
                    $creatorId   = auth('web')->id();
                    $creatorType = 'App\\Models\\User';
                } else {
                    abort(403, 'Unauthorized');
                }

                // Update booking main info
                $bookingData = $request->only([
                    'client_name',
                    'client_address',
                    'job_order_date',
                    'report_issue_to',
                    'reference_no',
                    'department_id',
                    'marketing_id',
                    'contact_no',
                    'contact_email',
                    'hold_status',
                    'booking_type', 
                ]);

                $bookingData['created_by_id']   = $creatorId;
                $bookingData['created_by_type'] = $creatorType;

                if ($request->hasFile('upload_letter_path')) {
                    $bookingData['upload_letter_path'] = $this->fileUploadService->upload(
                        $request->file('upload_letter_path'),
                        'bookings'
                    );
                }
                
                $new_booking->update($bookingData);

                // Remove all previous items
                $new_booking->items()->delete();

                // Insert new items if provided
                if ($request->has('booking_items')) {
                    foreach ($request->booking_items as $item) {
                        $new_booking->items()->create($item);
                    } 

                }
            });

            

            return redirect()
                ->back()
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
                ->back()
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
            ->where(function ($q) use ($query) {
                $q->where('user_code', 'like', '%' . $query . '%')
                ->orWhere('name', 'like', '%' . $query . '%');
            })
            ->get(['user_code', 'name'])
            ->map(function ($user) {
                return [
                    'user_code' => $user->user_code,
                    'name'      => $user->name,
                    'label'     => $user->user_code . ' - ' . $user->name,
                ];
            });

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
            ->where(function ($q) use ($query) {
                $q->where('user_code', 'like', '%' . $query . '%')
                ->orWhere('name', 'like', '%' . $query . '%');
            })
            ->get(['user_code', 'name'])
            ->map(function ($user) {
                return [
                    'user_code' => $user->user_code,
                    'name'      => $user->name,
                    'label'     => $user->user_code . ' - ' . $user->name,
                ];
            });

        return response()->json($results);
    }
}
