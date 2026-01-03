<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehicleService;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index()
    {
        $request = request();
        $query = Vehicle::with('services');

        if ($request->filled('search')) {
            $q = $request->get('search');
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('engine_number', 'like', "%{$q}%")
                   ->orWhere('handed_over_person', 'like', "%{$q}%");
            });
        }

        if ($request->filled('month')) {
            $query->whereMonth('rc_expiry_date', $request->get('month'));
        }

        if ($request->filled('year')) {
            $query->whereYear('rc_expiry_date', $request->get('year'));
        }

        // show most recently added vehicles first
        $vehicles = $query->orderByDesc('created_at')->get();

        // Provide variables used by the view; marketingPersons left empty for now
        $isAdmin = auth()->user() && method_exists(auth()->user(), 'isAdmin') ? auth()->user()->isAdmin() : false;
        $marketingPersons = [];

        return view('superadmin.vehicles.index', compact('vehicles', 'isAdmin', 'marketingPersons'));
    }

    public function create()
    {
        return view('superadmin.vehicles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'engine_number' => 'nullable|string|max:255',
            'handed_over_person' => 'nullable|string|max:255',
            'rc_expiry_date' => 'nullable|date',
            'puc_expiry_date' => 'nullable|date',
            'rc_copy' => 'nullable|file',
            'insurance' => 'nullable|file',
            'puc' => 'nullable|file',
        ]);

        $vehicle = new Vehicle();
        $vehicle->name = $data['name'];
        $vehicle->engine_number = $data['engine_number'] ?? null;
        $vehicle->handed_over_person = $data['handed_over_person'] ?? null;
        $vehicle->rc_expiry_date = $data['rc_expiry_date'] ?? null;
        $vehicle->puc_expiry_date = $data['puc_expiry_date'] ?? null;

        if ($request->hasFile('rc_copy')) {
            $vehicle->rc_copy_path = $request->file('rc_copy')->store('vehicles');
        }
        if ($request->hasFile('insurance')) {
            $vehicle->insurance_path = $request->file('insurance')->store('vehicles');
        }
        if ($request->hasFile('puc')) {
            $vehicle->puc_path = $request->file('puc')->store('vehicles');
        }

        $vehicle->save();

        return redirect()->route('superadmin.vehicles.index')->with('success', 'Vehicle created');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'engine_number' => 'nullable|string|max:255',
            'handed_over_person' => 'nullable|string|max:255',
            'rc_expiry_date' => 'nullable|date',
            'puc_expiry_date' => 'nullable|date',
            'rc_copy' => 'nullable|file',
            'insurance' => 'nullable|file',
            'puc' => 'nullable|file',
        ]);

        $vehicle->name = $data['name'];
        $vehicle->engine_number = $data['engine_number'] ?? null;
        $vehicle->handed_over_person = $data['handed_over_person'] ?? null;
        $vehicle->rc_expiry_date = $data['rc_expiry_date'] ?? null;
        $vehicle->puc_expiry_date = $data['puc_expiry_date'] ?? null;

        if ($request->hasFile('rc_copy')) {
            if (!empty($vehicle->rc_copy_path) && Storage::exists($vehicle->rc_copy_path)) {
                Storage::delete($vehicle->rc_copy_path);
            }
            $vehicle->rc_copy_path = $request->file('rc_copy')->store('vehicles');
        }
        if ($request->hasFile('insurance')) {
            if (!empty($vehicle->insurance_path) && Storage::exists($vehicle->insurance_path)) {
                Storage::delete($vehicle->insurance_path);
            }
            $vehicle->insurance_path = $request->file('insurance')->store('vehicles');
        }
        if ($request->hasFile('puc')) {
            if (!empty($vehicle->puc_path) && Storage::exists($vehicle->puc_path)) {
                Storage::delete($vehicle->puc_path);
            }
            $vehicle->puc_path = $request->file('puc')->store('vehicles');
        }

        $vehicle->save();

        if ($request->ajax() || $request->wantsJson()) {
            $vehicle->load('services');
            $html = view('superadmin.vehicles.partials.profile', compact('vehicle'))->render();
            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated',
                'profile_html' => $html,
            ]);
        }

        return redirect()->route('superadmin.vehicles.show', $vehicle->id)->with('success', 'Vehicle updated');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load('services');
        return view('superadmin.vehicles.show', compact('vehicle'));
    }

    /**
     * Return partial HTML for modal display of vehicle profile.
     */
    public function modal(Vehicle $vehicle)
    {
        $vehicle->load('services');
        return view('superadmin.vehicles.partials.profile', compact('vehicle'));
    }

    public function storeService(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'service_date' => 'required|date',
            'description' => 'nullable|string',
            'kilometers' => 'nullable|integer',
            'total_amount' => 'nullable|numeric',
            'person' => 'nullable|string|max:255',
            'service_bill' => 'nullable|file',
        ]);

        $service = new VehicleService($data);
        if ($request->hasFile('service_bill')) {
            $service->service_bill_path = $request->file('service_bill')->store('vehicles');
        }
        $vehicle->services()->save($service);

        // If AJAX request, return JSON with rendered profile partial so UI can update in-place
        if ($request->ajax() || $request->wantsJson()) {
            $vehicle->load('services');
            $html = view('superadmin.vehicles.partials.profile', compact('vehicle'))->render();
            return response()->json([
                'success' => true,
                'message' => 'Service recorded',
                'profile_html' => $html,
            ]);
        }

        return redirect()->route('superadmin.vehicles.show', $vehicle->id)->with('success', 'Service recorded');
    }

    public function downloadFile($path)
    {
        if (!Storage::exists($path)) {
            abort(404);
        }
        return Storage::download($path);
    }

    public function previewFile($path)
    {
        if (!Storage::exists($path)) {
            abort(404);
        }

        $mime = Storage::mimeType($path) ?? 'application/octet-stream';
        $stream = Storage::readStream($path);
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }

    /**
     * Remove the specified vehicle and its files.
     */
    public function destroy(Vehicle $vehicle)
    {
        // delete related service bills
        foreach ($vehicle->services as $s) {
            if (!empty($s->service_bill_path) && \Illuminate\Support\Facades\Storage::exists($s->service_bill_path)) {
                \Illuminate\Support\Facades\Storage::delete($s->service_bill_path);
            }
            // delete the service record
            $s->delete();
        }

        // delete vehicle files
        if (!empty($vehicle->rc_copy_path) && \Illuminate\Support\Facades\Storage::exists($vehicle->rc_copy_path)) {
            \Illuminate\Support\Facades\Storage::delete($vehicle->rc_copy_path);
        }
        if (!empty($vehicle->insurance_path) && \Illuminate\Support\Facades\Storage::exists($vehicle->insurance_path)) {
            \Illuminate\Support\Facades\Storage::delete($vehicle->insurance_path);
        }
        if (!empty($vehicle->puc_path) && \Illuminate\Support\Facades\Storage::exists($vehicle->puc_path)) {
            \Illuminate\Support\Facades\Storage::delete($vehicle->puc_path);
        }

        $vehicle->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Vehicle deleted']);
        }

        return redirect()->route('superadmin.vehicles.index')->with('success', 'Vehicle deleted');
    }
}
