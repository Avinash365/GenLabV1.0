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
use App\Services\FCMService; 
use App\Jobs\SendMarketingNotificationJob;
use Illuminate\Support\Facades\Cache;
 
 



class BookingController extends Controller
{
    protected GetUserActiveDepartment $departmentService;
    protected FileUploadService $fileUploadService;
    protected BookingCardService $bookingCardService;  
    protected FCMService $fcmService; 



    public function __construct(
        GetUserActiveDepartment $departmentService,
        FileUploadService $fileUploadService,  
        BookingCardService $bookingCardService, 
        FCMService $fcmService
    ) {
        $this->departmentService = $departmentService;
        $this->fileUploadService = $fileUploadService;
        $this->bookingCardService = $bookingCardService; 
        $this->fcmService          = $fcmService; 

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
                
                $department = Department::find($request->department_id);
 
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
                if ($department && strtolower($department->name) === 'bis') {
                    $bookingData['sample_code'] = $request->sample_code;
                }

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

            // ---------------------------
            // SEND NOTIFICATION TO MARKETING USER
            // ---------------------------
            $marketingUser = User::where('user_code', $request->marketing_id)->first();
            
            if ($marketingUser) {
                SendMarketingNotificationJob::dispatch(
                    $marketingUser,
                    "New Booking assigned!",
                    "{$booking->client_name} with Ref_No :{$request->reference_no}",
                    [
                        "client_name" => $booking->client_name, 
                        "booking_id" => $booking->id,
                        "updated_by" => auth()->id(),
                        "status"     => $booking->status
                    ]
                );
            }
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

                $department = Department::find($request->department_id);

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
                    'm_s', 
                ]);
                
                if ($department && strtolower($department->name) === 'bis') {
                    $bookingData['sample_code'] = $request->sample_code;
                }

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
        

            // ---------------------------
            // SEND NOTIFICATION TO MARKETING USER
            // ---------------------------
            $marketingUser = User::where('user_code', $request->marketing_id)->first();

            if ($marketingUser) {
                SendMarketingNotificationJob::dispatch(
                    $marketingUser,
                    "Booking Updated",
                    "Booking Ref_No :{$request->reference_no} has been updated.",
                    [
                        "booking_id" => $new_booking->id,
                        "updated_by" => auth()->user()->name ?? "System",
                        "client_name" => $new_booking->client_name, 
                    ]
                );
            } 

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

            $marketingUser = User::where('user_code', $new_booking->marketing_id)->first(); 
            $Ref_No =  $new_booking->reference_no; 
            $booking_id = $new_booking->id; 
            $client_name = $new_booking->client_name; 

            DB::transaction(function () use ($new_booking) {
                $new_booking->items()->delete();
                $new_booking->delete();
            }); 
            
            SendMarketingNotificationJob::dispatch(
                    $marketingUser,
                    "Booking Deleted",
                    "Booking Ref_No :{$Ref_No} has been deleted.",
                    [
                        "booking_id" => $booking_id,
                        "updated_by" => auth()->user()->name ?? "System",
                        "client_name" => $client_name, 
                    ]
            );

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
        $search = trim($request->query('term', ''));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $cacheKey = "job_order_" . md5($search);

        $results = Cache::remember($cacheKey, 30, function () use ($search) {
            return BookingItem::where('job_order_no', 'LIKE', "{$search}%") // prefix search
                ->distinct()
                ->orderBy('job_order_no', 'desc')
                ->limit(20)
                ->pluck('job_order_no');
        });

        return response()->json($results);
    }


    public function getAutocomplete(Request $request)
    {
        $term = trim($request->get('term', ''));
        $type = $request->get('type', '');

        if (strlen($term) < 2 || !in_array($type, ['lab', 'marketing'])) {
            return response()->json([]);
        }

        $roleSlug = $type === 'lab' ? 'lab_analyst' : 'marketing_person';

        $cacheKey = "user_auto_{$roleSlug}_" . md5($term);

        $users = Cache::remember($cacheKey, 30, function () use ($roleSlug, $term) {
            return User::whereHas('role', function ($q) use ($roleSlug) {
                    $q->where('slug', $roleSlug);
                })
                ->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', "{$term}%")
                    ->orWhere('user_code', 'LIKE', "{$term}%");
                })
                ->limit(20)
                ->get(['user_code', 'name'])
                ->map(function ($u) {
                    return [
                        'user_code' => $u->user_code,
                        'name' => $u->name,
                        'label' => $u->user_code . ' - ' . $u->name
                    ];
                });
        });

        return response()->json($users);
    }


    public function getReferenceNo(Request $request)
    {
        $term = trim($request->term ?? '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $cacheKey = "ref_search_" . md5($term);

        $results = Cache::remember($cacheKey, 30, function () use ($term) {
            return NewBooking::where('reference_no', 'LIKE', "{$term}%") // prefix search
                ->orderBy('reference_no')
                ->limit(20)
                ->pluck('reference_no')
                ->map(fn($ref) => ['reference_no' => $ref])
                ->toArray();
        });

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
