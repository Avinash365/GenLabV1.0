<?php 
namespace App\Http\Controllers\Accounts; 

use App\Http\Controllers\Controller; 


use Illuminate\Http\Request;
use App\Models\BookingItem;
use App\Models\Department; 
use App\Models\NewBooking; 
use App\Models\Client; 

use App\Services\GetUserActiveDepartment;

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

        $query = NewBooking::with(['items', 'department', 'marketingPerson']);

        // Department filter
        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
            $department = Department::find($departmentId);
        } else {
            $department = null;
        }

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhere('contact_email', 'like', "%{$search}%")
                ->orWhere('contact_no', 'like', "%{$search}%")
                ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "%{$search}%"))
                ->orWhereHas('items', fn($itemQ) => $itemQ->where('sample_description', 'like', "%{$search}%")
                                                            ->orWhere('sample_quality', 'like', "%{$search}%")
                                                            ->orWhere('lab_analysis_code', 'like', "%{$search}%")
                                                            ->orWhere('particulars', 'like', "%{$search}%"))
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

        $bookings = $query->latest()->paginate(10);
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
}