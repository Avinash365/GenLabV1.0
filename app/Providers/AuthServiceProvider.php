<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\User; 
use App\Models\Role; 
use App\Models\NewBooking;

use App\Policies\ProductPolicy;
use App\Policies\RolePolicy; 
use App\Policies\BookingPolicy;
use App\Policies\UserPolicy;





// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', 
        Product::class         => ProductPolicy::class,
        User::class            => UserPolicy::class, 
        Role::class            => RolePolicy::class, 
        NewBooking::class      => BookingPolicy::class, 
        
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
