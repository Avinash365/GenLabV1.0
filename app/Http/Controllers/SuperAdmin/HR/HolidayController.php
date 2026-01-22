<?php

namespace App\Http\Controllers\SuperAdmin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;

class HolidayController extends Controller
{
    public function index()
    {
        $perPage = (int) request('per_page', 10);
        $perPage = $perPage > 0 ? $perPage : 10;
        $holidays = Holiday::orderBy('date')->paginate($perPage)->withQueryString();
        return view('superadmin.hr.holidays', compact('holidays'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data['status'] = $data['status'] ?? 'active';
        Holiday::create($data);

        return redirect()->route('superadmin.hr.holidays.index')->with('success', 'Holiday added');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $holiday->update($data);

        return redirect()->route('superadmin.hr.holidays.index')->with('success', 'Holiday updated');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('superadmin.hr.holidays.index')->with('success', 'Holiday deleted');
    }
}
