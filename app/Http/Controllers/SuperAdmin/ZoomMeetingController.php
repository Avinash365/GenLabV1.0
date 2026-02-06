<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ZoomMeeting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Traits\ZoomSignatureTrait;

class ZoomMeetingController extends Controller
{
    use ZoomSignatureTrait;

    public function index()
    {
        $meetings = ZoomMeeting::with(['creator', 'attendees'])->latest()->get();
        return view('superadmin.zoom-meetings.index', compact('meetings'));
    }

    public function create()
    {
        return view('superadmin.zoom-meetings.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'start_time' => 'required|date',
            'duration' => 'required|integer',
            'agenda' => 'nullable|string',
            'join_url' => 'nullable|url',
        ]);

        $joinUrl = $request->join_url;
        $startUrl = null;
        $meetingId = null;

        // If manual link is NOT provided, try to create via Zoom API
        if (!$joinUrl) {
            $accountId = config('services.zoom.account_id');
            $clientId = config('services.zoom.client_id');
            $clientSecret = config('services.zoom.client_secret');

            // Debug: Check if config is loaded (Removing this in production)
            if (!$accountId || !$clientId || !$clientSecret) {
                return back()->withInput()->withErrors(['api_error' => 'Credentials missing. Please check .env and config/services.php']);
            }

            if ($accountId && $clientId && $clientSecret) {
                try {
                    // 1. Get Access Token
                    $response = Http::asForm()->withBasicAuth($clientId, $clientSecret)->post('https://zoom.us/oauth/token', [
                        'grant_type' => 'account_credentials',
                        'account_id' => $accountId,
                    ]);

                    if ($response->failed()) {
                         return back()->withInput()->withErrors(['api_error' => 'Zoom Token Error: ' . $response->status() . ' - ' . $response->body()]);
                    }

                    $accessToken = $response->json('access_token');

                    if ($accessToken) {
                        // 2. Create Meeting
                        $meetingResponse = Http::withToken($accessToken)->post('https://api.zoom.us/v2/users/me/meetings', [
                            'topic' => $request->topic,
                            'type' => 2, // Scheduled meeting
                            'start_time' => date('Y-m-d\TH:i:s', strtotime($request->start_time)),
                            'duration' => $request->duration,
                            'agenda' => $request->agenda,
                            // 'timezone' => 'Asia/Kolkata', // Optional: Verify timezone matches user preference
                        ]);

                        if ($meetingResponse->successful()) {
                            $data = $meetingResponse->json();
                            $joinUrl = $data['join_url'];
                            $startUrl = $data['start_url'];
                            $meetingId = (string)$data['id'];
                        } else {
                             return back()->withInput()->withErrors(['api_error' => 'Zoom API Error: ' . $meetingResponse->body()]);
                        }
                    } else {
                        return back()->withInput()->withErrors(['api_error' => 'Could not retrieve Zoom Access Token. Check credentials.']);
                    }
                } catch (\Exception $e) {
                    return back()->withInput()->withErrors(['api_error' => 'Connection Error: ' . $e->getMessage()]);
                }
            } else {
                // No keys and no manual link -> Error
                return back()->withInput()->withErrors(['join_url' => 'Please provide a Manual Zoom Link OR configure Zoom API credentials in .env file.']);
            }
        } else {
            // Manual link extraction logic (basic)
            $meetingId = $this->extractMeetingId($joinUrl);
            $startUrl = $joinUrl; // Host also joins via same link for manual
        }
        
        $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
        $userId = $user ? $user->id : null;
        $userType = $user ? get_class($user) : null;

        ZoomMeeting::create([
            'topic' => $request->topic,
            'start_time' => $request->start_time,
            'duration' => $request->duration,
            'agenda' => $request->agenda,
            'join_url' => $joinUrl,
            'start_url' => $startUrl,
            'meeting_id' => $meetingId,
            'created_by' => $userId,
            'created_by_type' => $userType,
            'status' => 'waiting',
        ]);

        return redirect()->route('superadmin.zoom-meetings.index')->with('success', 'Meeting created successfully.');
    }

    public function destroy($id)
    {
        $meeting = ZoomMeeting::findOrFail($id);
        $meeting->delete();
        return redirect()->route('superadmin.zoom-meetings.index')->with('success', 'Meeting deleted successfully.');
    }

    private function extractMeetingId($url)
    {
        // Simple regex to extract ID from standard zoom links
        // Matches /j/123456789 or /s/123456789
        if (preg_match('/\/[js]\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function join($id)
    {
        $meeting = ZoomMeeting::findOrFail($id);
        
        $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user() ?? request()->user();
        $userName = $user ? $user->name : 'Guest';

        if ($user) {
            // Check if already joined
            if (!$meeting->attendees()->where('user_id', $user->id)->exists()) {
                $meeting->attendees()->attach($user->id, ['joined_at' => now()]);
            }
        }

        // Generate Signature for Web SDK
        // Role: Force 0 (Participant) for embedded view to avoid "Signature Invalid" issues 
        // related to Host privileges matching the SDK App credentials.
        $role = 0;
        // if ($user && $meeting->created_by == $user->id && $meeting->created_by_type == get_class($user)) {
        //    $role = 1;
        // }

        $signature = $this->generateSignature($meeting->meeting_id, $role);

        return view('superadmin.zoom-meetings.meeting', compact('meeting', 'signature', 'userName'));
    }
}
