<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\NewBooking;   // ✅ use NewBooking instead of Booking
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        try {
           
            $clients = Client::withCount('bookings')->latest()->paginate(10);
            return view('superadmin.clients.index', compact('clients'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'nullable|email|unique:clients',
            'phone'  => 'nullable|string|max:20',
            'gstin'  => 'nullable|string|max:30',
            'address'=> 'nullable|string',
        ]);

        try {
            Client::create($request->all());
            return back()->with('success', 'Client registered successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $client = Client::findOrFail($id);
            $client->delete();
            return back()->with('success', 'Client deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ClientController.php
    public function assignBooking(Request $request, $bookingId)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        try {
            $booking = NewBooking::findOrFail($bookingId);
            $booking->client_id = $request->client_id;
            $booking->save();

            return back()->with('success', 'Client assigned successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}
