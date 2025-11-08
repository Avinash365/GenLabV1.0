<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\EmailSetting;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */

    public function boot(): void
    {
        // Share email accounts globally in all 'email.*' views
        View::composer('email.*', function ($view) {
            $emailAccounts = collect();

            if (auth('admin')->check()) {
                // Admin: show all email accounts
                $emailAccounts = EmailSetting::all();
            } elseif (auth()->check()) {
                // User: show only emails with permission
                $user = auth()->user();

                $emailAccounts = EmailSetting::all()->filter(function ($emailSetting) use ($user) {
                    // Create the same permission key pattern you stored earlier
                    $emailKey = str_replace(['@', '.'], '_', $emailSetting->email);

                    // Check if user has any of the permissions (view/edit/create/delete)
                    $actions = ['view', 'edit', 'create', 'delete'];

                    foreach ($actions as $action) {
                        if ($user->hasPermission("{$emailKey}.{$action}")) {
                            return true; // user has permission for this email
                        }
                    }

                    return false;
                });
            }

            $view->with('emailAccounts', $emailAccounts);
        });
    }

}
