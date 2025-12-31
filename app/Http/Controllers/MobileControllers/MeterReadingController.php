<?php

namespace App\Http\Controllers\MobileControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class MeterReadingController extends Controller
{
    /**
     * GET /api/meter-reading
     * Returns paginated list of meter readings for the authenticated mobile user.
     */
    public function index(Request $request)
    {
        // Use the API guard explicitly so JWT tokens are respected
        $user = $request->user('api') ?? auth('api')->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $roleName = '';
        if (isset($user->role)) {
            if (is_object($user->role) && isset($user->role->role_name)) {
                $roleName = $user->role->role_name;
            } elseif (is_string($user->role)) {
                $roleName = $user->role;
            }
        }
        $isAdmin = $roleName ? stripos($roleName, 'admin') !== false : false;

        $query = MeterReading::with('user')->orderByDesc('id');

        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->filled('marketing_person')) {
                $query->where('user_id', $request->marketing_person);
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('end_description', 'like', "%{$s}%");
            });
        }

        if ($request->filled('month')) {
            $m = (int) $request->month;
            $query->where(function ($q) use ($m) {
                $q->whereRaw('MONTH(starting_at) = ?', [$m])
                  ->orWhereRaw('MONTH(ending_at) = ?', [$m]);
            });
        }

        if ($request->filled('year')) {
            $y = (int) $request->year;
            $query->where(function ($q) use ($y) {
                $q->whereRaw('YEAR(starting_at) = ?', [$y])
                  ->orWhereRaw('YEAR(ending_at) = ?', [$y]);
            });
        }

        $perPage = in_array((int) $request->per_page, [25, 100, 250]) ? (int) $request->per_page : 25;
        $pag = $query->paginate($perPage)->appends($request->except('page'));

        $data = $pag->through(function ($r) {
            return [
                'id' => $r->id,
                'description' => $r->description,
                'starting_reading' => $r->starting_reading,
                'starting_at' => $r->starting_at ? $r->starting_at->toDateTimeString() : null,
                'starting_image' => $r->starting_image_path ? asset('storage/'.$r->starting_image_path) : null,
                'ending_reading' => $r->ending_reading,
                'ending_at' => $r->ending_at ? $r->ending_at->toDateTimeString() : null,
                'ending_image' => $r->ending_image_path ? asset('storage/'.$r->ending_image_path) : null,
                'total_reading' => $r->total_reading,
                'marketing_person' => $r->user ? [
                    'id' => $r->user->id,
                    'name' => $r->user->name,
                    'user_code' => $r->user->user_code ?? null,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $pag->currentPage(),
                'last_page' => $pag->lastPage(),
                'per_page' => $pag->perPage(),
                'total' => $pag->total(),
            ],
        ]);
    }

    /**
     * POST /api/meter-reading/upload
     * Accepts `current_reading` (required), optional `image`, optional `description`.
     * If the user has an open reading, this will close it; otherwise it creates a starting reading.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_reading' => 'required|numeric',
            'image' => 'nullable|image',
            'description' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Use the API guard explicitly so JWT tokens are respected
        $user = $request->user('api') ?? auth('api')->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $open = MeterReading::where('user_id', $user->id)
            ->whereNotNull('starting_reading')
            ->whereNull('ending_reading')
            ->first();

        if ($open) {
            $open->ending_reading = $request->current_reading;
            $open->ending_at = Carbon::now();
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('meter_readings', 'public');
                $open->ending_image_path = $path;
            }
            if ($request->filled('description')) {
                $open->end_description = $request->description;
            }
            if (is_numeric($open->starting_reading)) {
                $open->total_reading = $open->ending_reading - $open->starting_reading;
            }
            $open->save();

            $reading = $open;
            $message = 'Reading closed successfully';
            $status = 200;
        } else {
            $reading = new MeterReading();
            $reading->user_id = $user->id;
            $reading->starting_reading = $request->current_reading;
            $reading->starting_at = Carbon::now();
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('meter_readings', 'public');
                $reading->starting_image_path = $path;
            }
            if ($request->filled('description')) {
                $reading->start_description = $request->description;
            }
            $reading->save();

            $message = 'Reading started successfully';
            $status = 201;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'reading' => [
                'id' => $reading->id,
                'starting_reading' => $reading->starting_reading,
                'starting_at' => $reading->starting_at ? $reading->starting_at->toDateTimeString() : null,
                'starting_image' => $reading->starting_image_path ? asset('storage/'.$reading->starting_image_path) : null,
                'ending_reading' => $reading->ending_reading,
                'ending_at' => $reading->ending_at ? $reading->ending_at->toDateTimeString() : null,
                'ending_image' => $reading->ending_image_path ? asset('storage/'.$reading->ending_image_path) : null,
                'total_reading' => $reading->total_reading,
            ],
        ], $status);
    }
}
