<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // DRIVER authorizations
        Gate::define('ROLE_DRIVER_READ', function (User $user) {
            return in_array('ROLE_DRIVER_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_DRIVER_CREATE', function (User $user) {
            return in_array('ROLE_DRIVER_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_DRIVER_UPDATE', function (User $user) {
            return in_array('ROLE_DRIVER_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_DRIVER_DELETE', function (User $user) {
            return in_array('ROLE_DRIVER_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_DRIVER_PRINT', function (User $user) {
            return in_array('ROLE_DRIVER_PRINT', $user->roles)?:true;
        });

        // HOST authorizations
        Gate::define('ROLE_HOST_READ', function (User $user) {
            return in_array('ROLE_HOST_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_HOST_CREATE', function (User $user) {
            return in_array('ROLE_HOST_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_HOST_UPDATE', function (User $user) {
            return in_array('ROLE_HOST_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_HOST_DELETE', function (User $user) {
            return in_array('ROLE_HOST_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_HOST_PRINT', function (User $user) {
            return in_array('ROLE_HOST_PRINT', $user->roles)?:true;
        });

        // EMAIL_CHANNEL_PARAM authorizations
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_READ', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_CREATE', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_UPDATE', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_DELETE', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_PRINT', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_PRINT', $user->roles)?:true;
        });

        // INSTITUTION authorizations
        Gate::define('ROLE_INSTITUTION_READ', function (User $user) {
            return in_array('ROLE_INSTITUTION_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_INSTITUTION_CREATE', function (User $user) {
            return in_array('ROLE_INSTITUTION_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_INSTITUTION_UPDATE', function (User $user) {
            return in_array('ROLE_INSTITUTION_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_INSTITUTION_DELETE', function (User $user) {
            return in_array('ROLE_INSTITUTION_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_INSTITUTION_PRINT', function (User $user) {
            return in_array('ROLE_INSTITUTION_PRINT', $user->roles)?:true;
        });

        // SALE_POINT authorizations
        Gate::define('ROLE_SALE_POINT_READ', function (User $user) {
            return in_array('ROLE_SALE_POINT_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_SALE_POINT_CREATE', function (User $user) {
            return in_array('ROLE_SALE_POINT_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_SALE_POINT_UPDATE', function (User $user) {
            return in_array('ROLE_SALE_POINT_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_SALE_POINT_DELETE', function (User $user) {
            return in_array('ROLE_SALE_POINT_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_SALE_POINT_PRINT', function (User $user) {
            return in_array('ROLE_SALE_POINT_PRINT', $user->roles)?:true;
        });

        // CATEGORY authorizations
        Gate::define('ROLE_CATEGORY_READ', function (User $user) {
            return in_array('ROLE_CATEGORY_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_CATEGORY_CREATE', function (User $user) {
            return in_array('ROLE_CATEGORY_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_CATEGORY_UPDATE', function (User $user) {
            return in_array('ROLE_CATEGORY_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_CATEGORY_DELETE', function (User $user) {
            return in_array('ROLE_CATEGORY_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_CATEGORY_PRINT', function (User $user) {
            return in_array('ROLE_CATEGORY_PRINT', $user->roles)?:true;
        });

        // SUB_CATEGORY authorizations
        Gate::define('ROLE_SUB_CATEGORY_READ', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_SUB_CATEGORY_CREATE', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_SUB_CATEGORY_UPDATE', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_SUB_CATEGORY_DELETE', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_SUB_CATEGORY_PRINT', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_PRINT', $user->roles)?:true;
        });

        // PRODUCT authorizations
        Gate::define('ROLE_PRODUCT_READ', function (User $user) {
            return in_array('ROLE_PRODUCT_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_PRODUCT_CREATE', function (User $user) {
            return in_array('ROLE_PRODUCT_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_PRODUCT_UPDATE', function (User $user) {
            return in_array('ROLE_PRODUCT_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_PRODUCT_DELETE', function (User $user) {
            return in_array('ROLE_PRODUCT_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_PRODUCT_PRINT', function (User $user) {
            return in_array('ROLE_PRODUCT_PRINT', $user->roles)?:true;
        });

        // CLIENT authorizations
        Gate::define('ROLE_CLIENT_READ', function (User $user) {
            return in_array('ROLE_CLIENT_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_CLIENT_CREATE', function (User $user) {
            return in_array('ROLE_CLIENT_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_CLIENT_UPDATE', function (User $user) {
            return in_array('ROLE_CLIENT_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_CLIENT_DELETE', function (User $user) {
            return in_array('ROLE_CLIENT_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_CLIENT_PRINT', function (User $user) {
            return in_array('ROLE_CLIENT_PRINT', $user->roles)?:true;
        });

        // PROVIDER_TYPE authorizations
        Gate::define('ROLE_PROVIDER_TYPE_READ', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_PROVIDER_TYPE_CREATE', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_PROVIDER_TYPE_UPDATE', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_PROVIDER_TYPE_DELETE', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_PROVIDER_TYPE_PRINT', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_PRINT', $user->roles)?:true;
        });

        // PROVIDER authorizations
        Gate::define('ROLE_PROVIDER_READ', function (User $user) {
            return in_array('ROLE_PROVIDER_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_PROVIDER_CREATE', function (User $user) {
            return in_array('ROLE_PROVIDER_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_PROVIDER_UPDATE', function (User $user) {
            return in_array('ROLE_PROVIDER_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_PROVIDER_DELETE', function (User $user) {
            return in_array('ROLE_PROVIDER_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_PROVIDER_PRINT', function (User $user) {
            return in_array('ROLE_PROVIDER_PRINT', $user->roles)?:true;
        });

        // UNITY authorizations
        Gate::define('ROLE_UNITY_READ', function (User $user) {
            return in_array('ROLE_UNITY_READ', $user->roles)?:true;
        });
        Gate::define('ROLE_UNITY_CREATE', function (User $user) {
            return in_array('ROLE_UNITY_CREATE', $user->roles)?:true;
        });
        Gate::define('ROLE_UNITY_UPDATE', function (User $user) {
            return in_array('ROLE_UNITY_UPDATE', $user->roles)?:true;
        });
        Gate::define('ROLE_UNITY_DELETE', function (User $user) {
            return in_array('ROLE_UNITY_DELETE', $user->roles)?:true;
        });
        Gate::define('ROLE_UNITY_PRINT', function (User $user) {
            return in_array('ROLE_UNITY_PRINT', $user->roles)?:true;
        });


    }
}
