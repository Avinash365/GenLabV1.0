<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\MeterReading;

class MeterReadingController extends Controller
{
    public function index(Request $request)
    {
        $query = MeterReading::query();

        if ($request->filled('search')) {
            $q = $request->get('search');
            $query->where(function ($w) use ($q) {
                $w->where('start_description', 'like', "%{$q}%")
                  ->orWhere('end_description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('month')) {
            $query->whereMonth('created_at', (int) $request->get('month'));
        }
        if ($request->filled('year')) {
            $query->whereYear('created_at', (int) $request->get('year'));
        }

        $perPage = (int) $request->get('per_page', 25);
        $allowed = [25, 100, 250];
        if (!in_array($perPage, $allowed)) {
            $perPage = 25;
        }

        $rows = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->except('page'));

        $rows->getCollection()->transform(function ($r) {
            $endImage = $r->image_path ? asset('storage/' . ltrim($r->image_path, '/')) : null;
            $startImage = $r->starting_image_path ? asset('storage/' . ltrim($r->starting_image_path, '/')) : null;

            return [
                'id' => $r->id,
                'description' => $r->start_description ?: $r->end_description,
                'starting_reading' => $r->starting_reading,
                'starting_at' => $r->starting_at ? $r->starting_at->format('Y-m-d H:i:s') : null,
                'starting_image' => $startImage,
                'ending_reading' => $r->ending_reading,
                'ending_at' => $r->ending_at ? $r->ending_at->format('Y-m-d H:i:s') : null,
                'ending_image' => $endImage,
                'total_reading' => $r->total_reading,
                'end_description' => $r->end_description,
            ];
        });

        $hasOpen = MeterReading::whereNotNull('starting_reading')->whereNull('ending_reading')->exists();

        return view('superadmin.meter_reading.index', ['readings' => $rows, 'hasOpen' => $hasOpen]);
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'current_reading' => 'required|numeric',
            'image' => 'nullable|image|max:4096',
            'description' => 'nullable|string|max:1000',
        ]);

        // find an open reading (starting exists, no ending)
        $open = MeterReading::whereNotNull('starting_reading')->whereNull('ending_reading')->orderBy('created_at', 'desc')->first();

        if ($open) {
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('meter_readings', 'public');
                $open->image_path = $path;
            }

            $open->ending_reading = (float) $data['current_reading'];
            $open->ending_at = now();
            if (!empty($data['description'])) {
                $open->end_description = $data['description'];
            }

            if (is_numeric($open->starting_reading)) {
                $open->total_reading = $open->ending_reading - $open->starting_reading;
            }

            $open->save();

            return redirect()->route('superadmin.meter-reading.index')->with('success', 'Meter reading closed with ending value.');
        }

        // create starting reading (store starting image if provided)
        $payload = [
            'start_description' => $data['description'] ?? null,
            'starting_reading' => (float) $data['current_reading'],
            'starting_at' => now(),
            'user_id' => Auth::id(),
        ];

        if ($request->hasFile('image')) {
            $startPath = $request->file('image')->store('meter_readings', 'public');
            $payload['starting_image_path'] = $startPath;
        }

        $created = MeterReading::create($payload);

        return redirect()->route('superadmin.meter-reading.index')->with('success', 'Starting reading uploaded. Now upload ending reading to close it.');
    }
}
