<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\NewBooking;   //  use NewBooking instead of Booking
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:client.view')->only(['index']);
        $this->middleware('permission:client.create')->only(['store']);
        $this->middleware('permission:client.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10); // default 10

            $clients = Client::withCount('bookings')
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('gstin', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate($perPage)
                ->appends($request->query()); // keep filters on pagination

            return view('superadmin.accounts.client.index', compact('clients'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients',
            'phone' => 'nullable|string|max:20',
            'gstin' => 'nullable|string|max:30',
            'address' => 'nullable|string',
        ]);
        try {
            Client::create($request->all());
            return back()->with('success', 'Client registered successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'gstin' => 'nullable|string|max:30',
            'address' => 'nullable|string',
        ]);

        try {
            $client = Client::findOrFail($id);

            $client->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gstin' => $request->gstin,
                'address' => $request->address,
            ]);

            return back()->with('success', 'Client updated successfully!');
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


    // public function assignBulkBookings(Request $request)
    // {

    //     dd($request->all()); 
    //     exit; 
    //     $request->validate([
    //         'booking_ids' => 'required|array',
    //         'booking_ids.*' => 'exists:bookings,id',
    //         'client_id' => 'required|exists:clients,id',
    //     ]);

    //     NewBooking::whereIn('id', $request->booking_ids)
    //         ->update([
    //             'client_id' => $request->client_id
    //         ]);

    //     return back()->with('success', 'Client assigned successfully!');
    // }

    public function assignBulkBookings(Request $request)
    {
        $request->validate([
            'booking_ids' => 'required|array',
            'booking_ids.*' => 'exists:new_bookings,id',
            'client_id' => 'required|exists:clients,id',
        ]);

        NewBooking::whereIn('id', $request->booking_ids)
            ->update([
                'client_id' => $request->client_id
            ]);

        return back()->with('success', 'Client assigned successfully!');
    }


    public function unassignBooking($bookingId)
    {
        try {
            $booking = NewBooking::findOrFail($bookingId);

            $booking->client_id = null;
            $booking->save();

            return back()->with('success', 'Client unassigned successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function fetchAllBankTransaction(Request $request, $id)
    {
        try {
            $client = Client::findOrFail($id);

            $query = BankTransaction::whereHas('clients', function ($q) use ($id) {
                $q->where('client_id', $id);
            });

            // Optional filters
            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('date', $request->month);
            }

            if ($request->filled('bank_id')) {
                $query->where('bank_id', $request->bank_id);
            }

            // Debit / Credit filter
            if ($request->filled('type')) {
                if ($request->type === 'deposit') {
                    $query->whereNotNull('deposit')->where('deposit', '>', 0);
                } elseif ($request->type === 'withdrawal') {
                    $query->whereNotNull('withdrawal')->where('withdrawal', '>', 0);
                }
            }

            $transactions = $query
                ->orderBy('date', 'desc')
                ->paginate(10);

            return view(
                'superadmin.accounts.client.partials_bank_transactions',
                compact('transactions', 'client')
            )->render();

        } catch (\Exception $e) {
            Log::error('Client Bank Transaction Error', [
                'message' => $e->getMessage(),
                'client_id' => $id
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to load bank transactions'
            ], 500);
        }
    }

}
