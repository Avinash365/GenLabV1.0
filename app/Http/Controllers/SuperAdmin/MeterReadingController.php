<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\MeterReading;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MeterReadingsExport;

class MeterReadingController extends Controller
{

    public function __construct(){
        $this->middleware('permission:meter_reading.view')->only('index', 'exportPdf', 'exportExcel');
        $this->middleware('permission:meter_reading.create')->only('upload');
           
    }

    protected function buildQuery(Request $request)
    {
        $query = MeterReading::with('user');

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

        // If filter by marketing person (admin selecting), allow id, user_code or name
        if ($request->filled('marketing_person')) {
            $mp = $request->get('marketing_person');
            if (is_numeric($mp)) {
                $query->where('user_id', (int) $mp);
            } else {
                $query->whereHas('user', function($uq) use ($mp) {
                    $uq->where('user_code', $mp)
                       ->orWhere('name', 'like', "%{$mp}%");
                });
            }
        }

        // If the current user is a marketing person, restrict to their own readings
        $user = Auth::user();
        $roleName = '';
        if ($user) {
            $userRole = $user->role;
            if (is_object($userRole) && isset($userRole->role_name)) {
                $roleName = $userRole->role_name;
            } elseif (is_string($userRole)) {
                $roleName = $userRole;
            }
            if ($roleName && stripos($roleName, 'market') !== false) {
                $query->where('user_id', $user->id);
            }
        }
        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
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
                'marketing_person' => $r->user ? [
                    'id' => $r->user->id,
                    'name' => $r->user->name,
                    'user_code' => $r->user->user_code,
                ] : null,
                'ending_reading' => $r->ending_reading,
                'ending_at' => $r->ending_at ? $r->ending_at->format('Y-m-d H:i:s') : null,
                'ending_image' => $endImage,
                'total_reading' => $r->total_reading,
                'end_description' => $r->end_description,
            ];
        });

        // compute if the current user has an open starting reading (personal view)
        $hasOpenQuery = MeterReading::whereNotNull('starting_reading')->whereNull('ending_reading');
        $user = Auth::user(); // Re-fetch user in local scope or pass it or trust Auth::user()
        if ($user) {
            $hasOpenQuery->where('user_id', $user->id);
        }
        $hasOpen = $hasOpenQuery->exists();

        // Provide list of marketing persons for admin filter dropdown
        $marketingPersons = User::whereHas('role', function($r){
            $r->where('role_name', 'like', '%market%');
        })->orderBy('name')->get(['id','name','user_code']);

        return view('superadmin.meter_reading.index', ['readings' => $rows, 'hasOpen' => $hasOpen, 'marketingPersons' => $marketingPersons]);
    }

    public function exportPdf(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        $readings = $this->buildQuery($request)->orderBy('created_at', 'desc')->get();
        
        $pdf = Pdf::loadView('superadmin.meter_reading.pdf', compact('readings'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('meter_readings.pdf');
    }

    public function exportExcel(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        $readings = $this->buildQuery($request)->orderBy('created_at', 'desc')->get();
        return Excel::download(new MeterReadingsExport($readings), 'meter_readings.xlsx');
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'current_reading' => 'required|numeric',
            'image' => 'nullable|image|max:4096',
            'description' => 'nullable|string|max:1000',
        ]);

        // find an open reading belonging to the current user (starting exists, no ending)
        $open = MeterReading::whereNotNull('starting_reading')
            ->whereNull('ending_reading')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->first();

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
