<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewBooking; 

class ShowBookingController extends Controller
{
public function index()
    {
        // Fetch all bookings with their related items
        $bookings = NewBooking::with('items')->paginate(10);

        // Pass $bookings to the view
        return view('superadmin.showbooking.showbooking', compact('bookings'));
    }
}
