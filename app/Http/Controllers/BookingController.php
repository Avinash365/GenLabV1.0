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
use App\Models\{Department,SpecialFeature}; 
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
        $firstBackedBooking = SpecialFeature::orderBy('id')->first()?->backed_booking ?? 0;

        return view('superadmin.Bookings.newBooking', compact('departments', 'firstBackedBooking'));
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
                    'letter_date',
                    'job_order_date',
                    'department_id', 
                    'report_issue_to',
                    'reference_no',
                    'marketing_id',
                    'contact_no',
                    'contact_email',
                    'name_of_work', 
                    'hold_status',
                    'payment_option',  
                    'm_s', 
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
            
            return $this->bookingCardService->renderCardsForBooking($booking);            

        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors('Booking failed, please try again later.');
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
                    'letter_date',
                    'job_order_date',
                    'report_issue_to',
                    'reference_no',
                    'department_id',
                    'marketing_id',
                    'contact_no',
                    'contact_email',
                    'name_of_work', 
                    'hold_status',
                    'payment_option', 
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
            ->orderBy('job_order_no', 'desc')
            ->pluck('job_order_no');

        return response()->json($results);
    }

   

    public function getAutocomplete(Request $request)
    {
        $term = $request->get('term', '');
        $type = $request->get('type', ''); // 'lab' or 'marketing'

        if (empty($term) || !in_array($type, ['lab', 'marketing'])) {
            return response()->json([]);
        }

        $roleSlug = $type === 'lab' ? 'lab_analyst' : 'marketing_person';

        $users = User::whereHas('role', function($q) use ($roleSlug) {
                        $q->where('slug', $roleSlug); // Assuming role table has 'slug' column
                    })
                    ->where(function($q) use ($term) {
                        $q->where('name', 'LIKE', "%{$term}%")
                        ->orWhere('user_code', 'LIKE', "%{$term}%");
                    })
                    ->get(['user_code', 'name'])
                    ->map(function($user) {
                        return [
                            'user_code' => $user->user_code,
                            'name' => $user->name,
                            'label' => $user->user_code . ' - ' . $user->name
                        ];
                    });

        return response()->json($users);
    } 

    public function getReferenceNo(Request $request)
    {
        $term = $request->term ?? '';
        
        $results = NewBooking::where('reference_no', 'like', "%{$term}%")
            ->pluck('reference_no')
            ->map(fn($ref) => ['reference_no' => $ref])
            ->toArray();

        return response()->json($results);
    } 

    public function showBookingCards($bookingId, $itemId = null)
    {
        try {
            // Fetch booking with items
            $booking = NewBooking::with('items')->findOrFail($bookingId);

            // Pass item if provided
            $item = null;
            if ($itemId) {
                $item = $booking->items()->findOrFail($itemId);
            }

            // Return cards with or without item
            return $this->bookingCardService->renderCardsForBooking($booking, $item);

        } catch (\Exception $e) {
            Log::error('Failed to load booking cards', [
                'booking_id' => $bookingId,
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors('Unable to load booking details.');
        }
    } 


}
