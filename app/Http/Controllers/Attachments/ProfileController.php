<?php 
namespace App\Http\Controllers\Attachments;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Profile;

use App\Http\Controllers\Controller;

use App\Services\FileUploadService;



class ProfileController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
        $this->authorizeResource(Profile::class, 'profile');
    }

    // List all profiles
    public function index(Request $request)
    {
        $search = $request->input('search');

        if (auth()->guard('admin')->check()) {

            $profiles = Profile::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

            return view('superadmin.attachments.profile.index', compact('profiles'));
        }

       $profiles = Auth::user()->uploadedProfiles()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('superadmin.attachments.profile.index', compact('profiles'));
        
    }

    // Show create form
    public function create()
    {
        return view('superadmin.attachments.profile.create');
    }

    // Store new profile
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:512000', // ~500MB
        ]);

        try {
            $filePath = null;
            if ($request->hasFile('file')) {
                $filePath = $this->fileUploadService->upload($request->file('file'), 'profiles');
            }

            Profile::create([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'file_path'   => $filePath,
                'uploaded_by' => Auth::id(), 
            ]);

            return redirect()->back()->with('success', 'Profile created successfully!');
        } catch (\Exception $e) {
            Log::error('Profile Store Error: '.$e->getMessage());
            return back()->with('error', 'Something went wrong while saving profile. Please try again.');
        }
    }

    // Show single profile
    public function show(Profile $profile)
    {
        return view('profiles.show', compact('profile'));
    }

    // Edit form
    public function edit(Profile $profile)
    {
        return view('profiles.edit', compact('profile'));
    }

    // Update profile
    public function update(Request $request, Profile $profile, FileUploadService $fileUploadService)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:512000',
        ]);

        try {
            if ($request->hasFile('file')) {
                // Upload new file
                $validated['file_path'] = $this->fileUploadService->upload($request->file('file'),'profiles');
            }
            $profile->update($validated);

            return redirect()->back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            Log::error('Profile Update Error: '.$e->getMessage());
            return back()->with('error', 'Something went wrong while updating profile. Please try again.');
        }
    }

    // Delete profile
    public function destroy(Profile $profile)
    {
        try {
            
            $profile->delete();
            return redirect()->back()->with('success', 'Profile deleted successfully!');
        
        } catch (\Exception $e) {
            Log::error('Profile Delete Error: '.$e->getMessage());
            return back()->with('error', 'Something went wrong while deleting profile.');
        }
    }
}
