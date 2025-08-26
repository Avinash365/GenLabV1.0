<?php

namespace App\Http\Controllers;

use App\Models\Calibration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CalibrationController extends Controller
{
    
    public function __construct()
    {
        $this->authorizeResource(Calibration::class, 'calibration');
    }
    
    public function index(Request $request)
    {
        try {

            $search = $request->input('search'); 

             $calibrations = Calibration::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('agency_name', 'like', "%{$search}%")
                      ->orWhere('equipment_name', 'like', "%{$search}%")
                      ->orWhere('issue_date', 'like', "%{$search}%")
                      ->orWhere('expire_date', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);
            
            return view('superadmin.calibrations.index', compact('calibrations'));
        } catch (\Exception $e) {
            Log::error('Calibration Index Error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Unable to fetch calibrations');
        }
    }

    public function create()
    {
        return view('superadmin.calibrations.create');
    }

    public function store(Request $request)
    {
         
            $validated = $request->validate([
                'agency_name'    => 'required|string|max:255',
                'equipment_name' => 'required|string|max:255',
                'issue_date'     => 'required|date',
                'expire_date'    => 'required|date|after_or_equal:issue_date',
            ]);

        try {

            $validated['created_by'] = Auth::id();

            Calibration::create($validated);

            return redirect()->back()->with('success', 'Calibration created successfully.');
        } catch (\Exception $e) {
            Log::error('Calibration Store Error: '.$e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Unable to create calibration.');
        }
    }

    public function show(Calibration $calibration)
    {
        try {
            return view('calibrations.show', compact('calibration'));
        } catch (\Exception $e) {
            Log::error('Calibration Show Error: '.$e->getMessage());
            return redirect()->route('calibrations.index')->with('error', 'Unable to fetch calibration.');
        }
    }

    public function edit(Calibration $calibration)
    {
        return view('calibrations.edit', compact('calibration'));
    }

    public function update(Request $request, Calibration $calibration)
    {
        try {
            $validated = $request->validate([
                'agency_name'    => 'required|string|max:255',
                'equipment_name' => 'required|string|max:255',
                'issue_date'     => 'required|date',
                'expire_date'    => 'required|date|after_or_equal:issue_date',
            ]);

            $calibration->update($validated);

            return redirect()->back()->with('success', 'Calibration updated successfully.');
        } catch (\Exception $e) {
            Log::error('Calibration Update Error: '.$e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Unable to update calibration.');
        }
    }

    public function destroy(Calibration $calibration)
    {
        try {
            $calibration->delete();
            return redirect()->back()->with('success', 'Calibration deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Calibration Delete Error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Unable to delete calibration.');
        }
    }
}
