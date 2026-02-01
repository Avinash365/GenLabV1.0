<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission; 
use Illuminate\Http\Request;
use App\Services\UserRegistroService; 
use App\Models\User; 
use App\Jobs\SendMarketingNotificationJob; 
use Illuminate\Support\Facades\Schema;


class UserController extends Controller
{
    
    protected UserRegistroService $service;

    public function __construct(UserRegistroService $service)
    {
        $this->service = $service;
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
       return $this->service->index(); 
    }

    public function create()
    {
       return $this->service->create(); 
    }  

    public function store(Request $request)
    {
        return $this->service->store($request);
    }

    
    public function update(Request $request, User $user)
    {
        return $this->service->update($request, $user);
    }

    public function updatePermissions(Request $request, User $user){
        
        $this->authorize('update', $user);
        return $this->service->updatePermissions($request, $user);
    }

    public function destroy(User $user)
    {
        return $this->service->delete($user);
    } 

    public function sendNotification(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'image' => 'nullable|image|max:2048', // 2MB
        ]);

        $user = User::findOrFail($id);

        // Handle image upload if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/notifications');
            $imagePath = str_replace('public/', 'storage/', $imagePath);
        }

        // Example: Send notification via Job
        SendMarketingNotificationJob::dispatch(
            $user,
            "Admin Notification",
            $request->message,
            [
                "image" => $imagePath,
            ]
        );

        return back()->with('success', "Notification sent to {$user->name}.");
    }

    /**
     * Toggle user's active status (activate / deactivate)
     */
    public function toggleStatus(Request $request, $id)
    {
        // Use withoutGlobalScope('active') so we can toggle even when the user is deactivated.
        $user = User::withoutGlobalScope('active')->findOrFail($id);
        $this->authorize('update', $user);
        // Support common field names: is_active (bool), active (bool), status (string)
        $table = $user->getTable();

        if (Schema::hasColumn($table, 'is_active')) {
            $user->is_active = !$user->is_active;
            $user->save();
            $state = $user->is_active;
            $messageState = $state ? 'activated' : 'deactivated';

            return back()->with('success', "User {$user->name} has been {$messageState}.");
        }

        if (Schema::hasColumn($table, 'active')) {
            $user->active = !$user->active;
            $user->save();
            $state = $user->active;
            $messageState = $state ? 'activated' : 'deactivated';

            return back()->with('success', "User {$user->name} has been {$messageState}.");
        }

        if (Schema::hasColumn($table, 'status')) {
            $user->status = ($user->status === 'active') ? 'inactive' : 'active';
            $user->save();
            $state = ($user->status === 'active');
            $messageState = $state ? 'activated' : 'deactivated';

            return back()->with('success', "User {$user->name} has been {$messageState}.");
        }

        // If none of the expected columns exist, do not attempt to write unknown columns.
        return back()->with('error', 'Activate/Deactivate is not supported for the current users table schema.');
    }


}