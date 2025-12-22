<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Authorize private chat channels: chat.user.{type}.{id}
Broadcast::channel('chat.user.{type}.{id}', function ($user, $type, $id) {
    try {
        // allow if the authenticated user matches the requested type and id
        if ($type === 'admin' && $user instanceof \App\Models\Admin && (int)$user->id === (int)$id) return true;
        if ($type === 'employee' && $user instanceof \App\Models\Employee && (int)$user->id === (int)$id) return true;
        if ($type === 'user' && $user instanceof \App\Models\User && (int)$user->id === (int)$id) return true;
    } catch (\Exception $e) {
        // fallback deny
    }
    return false;
});
