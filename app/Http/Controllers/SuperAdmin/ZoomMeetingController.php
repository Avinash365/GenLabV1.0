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
        $meetings = ZoomMeeting::with(['creator', 'meetingAttendees'])->latest()->get();
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
            'agenda' => 'nullable|string',
            'join_url' => 'nullable|url',
        ]);

        $joinUrl = $request->join_url;
        $startUrl = null;
        $meetingId = null;
        $password = null;
        
        // Default duration 40 mins if not provided (Zoom basic limit is 40, Pro is unlimited)
        // If creating via API, we send this. If joining manual, this acts as placeholder until fetched.
        $duration = 40; 
        
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
                            'duration' => $duration,
                            'agenda' => $request->agenda,
                            'settings' => [
                                'auto_recording' => 'cloud',
                            ],
                            // 'timezone' => 'Asia/Kolkata', // Optional: Verify timezone matches user preference
                        ]);

                        if ($meetingResponse->successful()) {
                            $data = $meetingResponse->json();
                            $joinUrl = $data['join_url'];
                            $startUrl = $data['start_url'];
                            $meetingId = (string)$data['id'];
                            $password = $data['password'] ?? null;
                            $duration = $data['duration'] ?? $duration; // Update from actual response
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

            // Attempt to fetch duration from Zoom if we have an ID
            if ($meetingId) {
                 $token = $this->getAccessToken();
                 if ($token) {
                     $detailsResponse = Http::withToken($token)->get("https://api.zoom.us/v2/meetings/{$meetingId}");
                     if ($detailsResponse->successful()) {
                         $details = $detailsResponse->json();
                         $duration = $details['duration'] ?? $duration;
                         $password = $details['password'] ?? $password; // Better password source
                     }
                 }
            }
        }
        
        $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
        $userId = $user ? $user->id : null;
        $userType = $user ? get_class($user) : null;

        ZoomMeeting::create([
            'topic' => $request->topic,
            'start_time' => $request->start_time,
            'duration' => $duration,
            'agenda' => $request->agenda,
            'join_url' => $joinUrl,
            'start_url' => $startUrl,
            'meeting_id' => $meetingId,
            'password' => $password,
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

    private function getAccessToken()
    {
        $accountId = config('services.zoom.account_id');
        $clientId = config('services.zoom.client_id');
        $clientSecret = config('services.zoom.client_secret');

        if (!$accountId || !$clientId || !$clientSecret) {
            return null;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post('https://zoom.us/oauth/token', [
                    'grant_type' => 'account_credentials',
                    'account_id' => $accountId,
                ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    private function extractPassword($url)
    {
        $parsed = parse_url($url);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
            return $query['pwd'] ?? null;
        }
        return null;
    }

    private function recoverPasswordIfNeeded($meeting)
    {
        if (empty($meeting->password) && $meeting->meeting_id) {
             $token = $this->getAccessToken();
             if ($token) {
                 $response = Http::withToken($token)->get("https://api.zoom.us/v2/meetings/{$meeting->meeting_id}");
                 if ($response->successful()) {
                     $data = $response->json();
                     if (isset($data['password'])) {
                         $meeting->password = $data['password'];
                         $meeting->save();
                     }
                 }
             }
        }
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

    public function syncRecording($id)
    {
        $meeting = ZoomMeeting::findOrFail($id);
        
        if (!$meeting->meeting_id) {
             return back()->withErrors(['api_error' => 'No Zoom Meeting ID found for this meeting.']);
        }

        $token = $this->getAccessToken();
        if (!$token) {
            return back()->withErrors(['api_error' => 'Could not authenticate with Zoom.']);
        }

        // Fetch recordings
        $response = Http::withToken($token)->get("https://api.zoom.us/v2/meetings/{$meeting->meeting_id}/recordings");

        if ($response->successful()) {
            $data = $response->json();
            
            // Zoom returns a list of recording files. We usually want the "share_url" which is a public viewer link.
            // Or "recording_files" array if we want specific mp4 files.
            // The "share_url" is at the top level of the response.
            
            $shareUrl = $data['share_url'] ?? null;
            
            if ($shareUrl) {
                // If the share URL requires password and it is provided in 'password' field of recording response
                // But usually share_url includes it or it is a separate setting. 
                
                $meeting->recording_url = $shareUrl;
                $meeting->save();
                
                return back()->with('success', 'Recording synced successfully.');
            } else {
                 return back()->with('info', 'No recording found for this meeting yet.');
            }
        } else {
             // 404 means no recording found usually
             if ($response->status() == 404) {
                 return back()->with('info', 'No cloud recording found on Zoom servers for this meeting.');
             }
             return back()->withErrors(['api_error' => 'Zoom API Error: ' . $response->body()]);
        }
    }

    public function join($id)
    {
        $meeting = ZoomMeeting::findOrFail($id);
        
        $this->recoverPasswordIfNeeded($meeting);
        
        $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user() ?? request()->user();
        $userName = $user ? $user->name : 'Guest';

        if ($user) {
            // Check if already joined using polymorphic check logic
            $userType = get_class($user);
            $existing = \DB::table('zoom_meeting_attendees')
                        ->where('zoom_meeting_id', $meeting->id)
                        ->where('user_id', $user->id)
                        ->where('user_type', $userType)
                        ->exists();

            if (!$existing) {
                \DB::table('zoom_meeting_attendees')->insert([
                    'zoom_meeting_id' => $meeting->id,
                    'user_id' => $user->id,
                    'user_type' => $userType,
                    'attendee_name' => $userName,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Generate Signature for Web SDK
        // Role: Check if the user is the creator (Host = 1), otherwise Participant (0)
        $role = 0;
        if ($user && $meeting->created_by == $user->id) {
           $role = 1;
        }

        $signature = $this->generateSignature($meeting->meeting_id, $role);

        return view('superadmin.zoom-meetings.meeting', compact('meeting', 'signature', 'userName'));
    }
}
