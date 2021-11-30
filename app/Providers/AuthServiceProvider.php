<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        if (Schema::hasTable('roles')) {
            $roles = Role::all();
            if ($roles) {
                foreach ($roles as $key => $role) {
                    Gate::define($role->code, function (User $user) use ($role) {
                        return (in_array($role->code, $user->roles) || in_array('ADMIN', $user->roles)) ? Response::allow() : abort(403);
                    });
                }
            }
        }
    }
}
