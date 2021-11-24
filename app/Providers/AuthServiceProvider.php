<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
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

        // $json_roles = File::get('database/data/role.json');
        // $roles = json_decode($json_roles);
        // dd($roles);

        // foreach ($roles as $key => $role) {
        //     Gate::define($role->code, function (User $user) use ($role) {
        //         return in_array($role->code, $user->roles) ? Response::allow() : abort(403);
        //     });
        // }
        // DRIVER authorizations
        Gate::define('ROLE_DRIVER_READ', function (User $user) {
            return in_array('ROLE_DRIVER_READ', $user->roles) ? Response::allow() : abort(403);
        });
        /*Gate::define('ROLE_DRIVER_CREATE', function (User $user) {
            return in_array('ROLE_DRIVER_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DRIVER_UPDATE', function (User $user) {
            return in_array('ROLE_DRIVER_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DRIVER_DELETE', function (User $user) {
            return in_array('ROLE_DRIVER_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DRIVER_PRINT', function (User $user) {
            return in_array('ROLE_DRIVER_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // HOST authorizations
        Gate::define('ROLE_HOST_READ', function (User $user) {
            return in_array('ROLE_HOST_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_HOST_CREATE', function (User $user) {
            return in_array('ROLE_HOST_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_HOST_UPDATE', function (User $user) {
            return in_array('ROLE_HOST_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_HOST_DELETE', function (User $user) {
            return in_array('ROLE_HOST_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_HOST_PRINT', function (User $user) {
            return in_array('ROLE_HOST_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // EMAIL_CHANNEL_PARAM authorizations
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_READ', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_CREATE', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_UPDATE', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_DELETE', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_EMAIL_CHANNEL_PARAM_PRINT', function (User $user) {
            return in_array('ROLE_EMAIL_CHANNEL_PARAM_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // INSTITUTION authorizations
        Gate::define('ROLE_INSTITUTION_READ', function (User $user) {
            return in_array('ROLE_INSTITUTION_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_INSTITUTION_CREATE', function (User $user) {
            return in_array('ROLE_INSTITUTION_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_INSTITUTION_UPDATE', function (User $user) {
            return in_array('ROLE_INSTITUTION_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_INSTITUTION_DELETE', function (User $user) {
            return in_array('ROLE_INSTITUTION_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_INSTITUTION_PRINT', function (User $user) {
            return in_array('ROLE_INSTITUTION_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // SALE_POINT authorizations
        Gate::define('ROLE_SALE_POINT_READ', function (User $user) {
            return in_array('ROLE_SALE_POINT_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_POINT_CREATE', function (User $user) {
            return in_array('ROLE_SALE_POINT_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_POINT_UPDATE', function (User $user) {
            return in_array('ROLE_SALE_POINT_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_POINT_DELETE', function (User $user) {
            return in_array('ROLE_SALE_POINT_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_POINT_PRINT', function (User $user) {
            return in_array('ROLE_SALE_POINT_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // CATEGORY authorizations
        Gate::define('ROLE_CATEGORY_READ', function (User $user) {
            return in_array('ROLE_CATEGORY_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CATEGORY_CREATE', function (User $user) {
            return in_array('ROLE_CATEGORY_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CATEGORY_UPDATE', function (User $user) {
            return in_array('ROLE_CATEGORY_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CATEGORY_DELETE', function (User $user) {
            return in_array('ROLE_CATEGORY_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CATEGORY_PRINT', function (User $user) {
            return in_array('ROLE_CATEGORY_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // SUB_CATEGORY authorizations
        Gate::define('ROLE_SUB_CATEGORY_READ', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SUB_CATEGORY_CREATE', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SUB_CATEGORY_UPDATE', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SUB_CATEGORY_DELETE', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SUB_CATEGORY_PRINT', function (User $user) {
            return in_array('ROLE_SUB_CATEGORY_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // PRODUCT authorizations
        Gate::define('ROLE_PRODUCT_READ', function (User $user) {
            return in_array('ROLE_PRODUCT_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PRODUCT_CREATE', function (User $user) {
            return in_array('ROLE_PRODUCT_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PRODUCT_UPDATE', function (User $user) {
            return in_array('ROLE_PRODUCT_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PRODUCT_DELETE', function (User $user) {
            return in_array('ROLE_PRODUCT_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PRODUCT_PRINT', function (User $user) {
            return in_array('ROLE_PRODUCT_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // CLIENT authorizations
        Gate::define('ROLE_CLIENT_READ', function (User $user) {
            return in_array('ROLE_CLIENT_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_CREATE', function (User $user) {
            return in_array('ROLE_CLIENT_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_UPDATE', function (User $user) {
            return in_array('ROLE_CLIENT_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_DELETE', function (User $user) {
            return in_array('ROLE_CLIENT_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_PRINT', function (User $user) {
            return in_array('ROLE_CLIENT_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // PROVIDER_TYPE authorizations
        Gate::define('ROLE_PROVIDER_TYPE_READ', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PROVIDER_TYPE_CREATE', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PROVIDER_TYPE_UPDATE', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PROVIDER_TYPE_DELETE', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PROVIDER_TYPE_PRINT', function (User $user) {
            return in_array('ROLE_PROVIDER_TYPE_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // PROVIDER authorizations
        Gate::define('ROLE_PROVIDER_READ', function (User $user) {
            return in_array('ROLE_PROVIDER_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PROVIDER_CREATE', function (User $user) {
            return in_array('ROLE_PROVIDER_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PROVIDER_UPDATE', function (User $user) {
            return in_array('ROLE_PROVIDER_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PROVIDER_DELETE', function (User $user) {
            return in_array('ROLE_PROVIDER_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PROVIDER_PRINT', function (User $user) {
            return in_array('ROLE_PROVIDER_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // UNITY authorizations
        Gate::define('ROLE_UNITY_READ', function (User $user) {
            return in_array('ROLE_UNITY_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_UNITY_CREATE', function (User $user) {
            return in_array('ROLE_UNITY_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_UNITY_UPDATE', function (User $user) {
            return in_array('ROLE_UNITY_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_UNITY_DELETE', function (User $user) {
            return in_array('ROLE_UNITY_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_UNITY_PRINT', function (User $user) {
            return in_array('ROLE_UNITY_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // STOCK_TYPE authorizations
        Gate::define('ROLE_STOCK_TYPE_READ', function (User $user) {
            return in_array('ROLE_STOCK_TYPE_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_STOCK_TYPE_CREATE', function (User $user) {
            return in_array('ROLE_STOCK_TYPE_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_STOCK_TYPE_UPDATE', function (User $user) {
            return in_array('ROLE_STOCK_TYPE_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_STOCK_TYPE_DELETE', function (User $user) {
            return in_array('ROLE_STOCK_TYPE_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_STOCK_TYPE_PRINT', function (User $user) {
            return in_array('ROLE_STOCK_TYPE_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // ORDER authorizations
        Gate::define('ROLE_ORDER_READ', function (User $user) {
            return in_array('ROLE_ORDER_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_ORDER_CREATE', function (User $user) {
            return in_array('ROLE_ORDER_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_ORDER_UPDATE', function (User $user) {
            return in_array('ROLE_ORDER_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_ORDER_DELETE', function (User $user) {
            return in_array('ROLE_ORDER_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_ORDER_PRINT', function (User $user) {
            return in_array('ROLE_ORDER_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_ORDER_VALIDATE', function (User $user) {
            return in_array('ROLE_ORDER_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_ORDER_REJECT', function (User $user) {
            return in_array('ROLE_ORDER_REJECT', $user->roles) ? Response::allow() : abort(403);
        });

        // PURCHASE authorizations
        Gate::define('ROLE_PURCHASE_READ', function (User $user) {
            return in_array('ROLE_PURCHASE_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_CREATE', function (User $user) {
            return in_array('ROLE_PURCHASE_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_UPDATE', function (User $user) {
            return in_array('ROLE_PURCHASE_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_DELETE', function (User $user) {
            return in_array('ROLE_PURCHASE_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_PRINT', function (User $user) {
            return in_array('ROLE_PURCHASE_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_VALIDATE', function (User $user) {
            return in_array('ROLE_PURCHASE_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_REJECT', function (User $user) {
            return in_array('ROLE_PURCHASE_REJECT', $user->roles) ? Response::allow() : abort(403);
        });

        // DELIVERY_NOTE authorizations
        Gate::define('ROLE_DELIVERY_NOTE_READ', function (User $user) {
            return in_array('ROLE_DELIVERY_NOTE_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DELIVERY_NOTE_CREATE', function (User $user) {
            return in_array('ROLE_DELIVERY_NOTE_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DELIVERY_NOTE_UPDATE', function (User $user) {
            return in_array('ROLE_DELIVERY_NOTE_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DELIVERY_NOTE_DELETE', function (User $user) {
            return in_array('ROLE_DELIVERY_NOTE_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DELIVERY_NOTE_PRINT', function (User $user) {
            return in_array('ROLE_DELIVERY_NOTE_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DELIVERY_NOTE_VALIDATE', function (User $user) {
            return in_array('ROLE_DELIVERY_NOTE_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DELIVERY_NOTE_REJECT', function (User $user) {
            return in_array('ROLE_DELIVERY_NOTE_REJECT', $user->roles) ? Response::allow() : abort(403);
        });

        // PURCHASE_ORDER authorizations
        Gate::define('ROLE_PURCHASE_ORDER_READ', function (User $user) {
            return in_array('ROLE_PURCHASE_ORDER_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_ORDER_CREATE', function (User $user) {
            return in_array('ROLE_PURCHASE_ORDER_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_ORDER_UPDATE', function (User $user) {
            return in_array('ROLE_PURCHASE_ORDER_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_ORDER_DELETE', function (User $user) {
            return in_array('ROLE_PURCHASE_ORDER_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_ORDER_PRINT', function (User $user) {
            return in_array('ROLE_PURCHASE_ORDER_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_ORDER_VALIDATE', function (User $user) {
            return in_array('ROLE_PURCHASE_ORDER_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_PURCHASE_ORDER_REJECT', function (User $user) {
            return in_array('ROLE_PURCHASE_ORDER_REJECT', $user->roles) ? Response::allow() : abort(403);
        });

        // SALE authorizations
        Gate::define('ROLE_SALE_READ', function (User $user) {
            return in_array('ROLE_SALE_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_CREATE', function (User $user) {
            return in_array('ROLE_SALE_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_UPDATE', function (User $user) {
            return in_array('ROLE_SALE_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_DELETE', function (User $user) {
            return in_array('ROLE_SALE_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_PRINT', function (User $user) {
            return in_array('ROLE_SALE_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_VALIDATE', function (User $user) {
            return in_array('ROLE_SALE_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_SALE_REJECT', function (User $user) {
            return in_array('ROLE_SALE_REJECT', $user->roles) ? Response::allow() : abort(403);
        });

        // CLIENT_DELIVERY_NOTE authorizations
        Gate::define('ROLE_CLIENT_DELIVERY_NOTE_READ', function (User $user) {
            return in_array('ROLE_CLIENT_DELIVERY_NOTE_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_DELIVERY_NOTE_CREATE', function (User $user) {
            return in_array('ROLE_CLIENT_DELIVERY_NOTE_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_DELIVERY_NOTE_UPDATE', function (User $user) {
            return in_array('ROLE_CLIENT_DELIVERY_NOTE_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_DELIVERY_NOTE_DELETE', function (User $user) {
            return in_array('ROLE_CLIENT_DELIVERY_NOTE_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_DELIVERY_NOTE_PRINT', function (User $user) {
            return in_array('ROLE_CLIENT_DELIVERY_NOTE_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_DELIVERY_NOTE_VALIDATE', function (User $user) {
            return in_array('ROLE_CLIENT_DELIVERY_NOTE_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_CLIENT_DELIVERY_NOTE_REJECT', function (User $user) {
            return in_array('ROLE_CLIENT_DELIVERY_NOTE_REJECT', $user->roles) ? Response::allow() : abort(403);
        });

        // COMPARTMENT authorizations
        Gate::define('ROLE_COMPARTMENT_READ', function (User $user) {
            return in_array('ROLE_COMPARTMENT_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_COMPARTMENT_CREATE', function (User $user) {
            return in_array('ROLE_COMPARTMENT_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_COMPARTMENT_UPDATE', function (User $user) {
            return in_array('ROLE_COMPARTMENT_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_COMPARTMENT_DELETE', function (User $user) {
            return in_array('ROLE_COMPARTMENT_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_COMPARTMENT_PRINT', function (User $user) {
            return in_array('ROLE_COMPARTMENT_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // FOLDER authorizations
        Gate::define('ROLE_FOLDER_READ', function (User $user) {
            return in_array('ROLE_FOLDER_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_FOLDER_CREATE', function (User $user) {
            return in_array('ROLE_FOLDER_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_FOLDER_UPDATE', function (User $user) {
            return in_array('ROLE_FOLDER_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_FOLDER_DELETE', function (User $user) {
            return in_array('ROLE_FOLDER_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_FOLDER_PRINT', function (User $user) {
            return in_array('ROLE_FOLDER_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // TANK authorizations
        Gate::define('ROLE_TANK_READ', function (User $user) {
            return in_array('ROLE_TANK_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TANK_CREATE', function (User $user) {
            return in_array('ROLE_TANK_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TANK_UPDATE', function (User $user) {
            return in_array('ROLE_TANK_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TANK_DELETE', function (User $user) {
            return in_array('ROLE_TANK_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TANK_PRINT', function (User $user) {
            return in_array('ROLE_TANK_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // TRUCK authorizations
        Gate::define('ROLE_TRUCK_READ', function (User $user) {
            return in_array('ROLE_TRUCK_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRUCK_CREATE', function (User $user) {
            return in_array('ROLE_TRUCK_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRUCK_UPDATE', function (User $user) {
            return in_array('ROLE_TRUCK_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRUCK_DELETE', function (User $user) {
            return in_array('ROLE_TRUCK_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRUCK_PRINT', function (User $user) {
            return in_array('ROLE_TRUCK_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // TOURN authorizations
        Gate::define('ROLE_TOURN_READ', function (User $user) {
            return in_array('ROLE_TOURN_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TOURN_CREATE', function (User $user) {
            return in_array('ROLE_TOURN_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TOURN_UPDATE', function (User $user) {
            return in_array('ROLE_TOURN_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TOURN_DELETE', function (User $user) {
            return in_array('ROLE_TOURN_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TOURN_PRINT', function (User $user) {
            return in_array('ROLE_TOURN_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // DESTINATION authorizations
        Gate::define('ROLE_DESTINATION_READ', function (User $user) {
            return in_array('ROLE_DESTINATION_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DESTINATION_CREATE', function (User $user) {
            return in_array('ROLE_DESTINATION_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DESTINATION_UPDATE', function (User $user) {
            return in_array('ROLE_DESTINATION_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DESTINATION_DELETE', function (User $user) {
            return in_array('ROLE_DESTINATION_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_DESTINATION_PRINT', function (User $user) {
            return in_array('ROLE_DESTINATION_PRINT', $user->roles) ? Response::allow() : abort(403);
        });

        // GOOD_TO_REMOVE authorizations
        Gate::define('ROLE_GOOD_TO_REMOVE_READ', function (User $user) {
            return in_array('ROLE_GOOD_TO_REMOVE_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_GOOD_TO_REMOVE_CREATE', function (User $user) {
            return in_array('ROLE_GOOD_TO_REMOVE_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_GOOD_TO_REMOVE_UPDATE', function (User $user) {
            return in_array('ROLE_GOOD_TO_REMOVE_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_GOOD_TO_REMOVE_DELETE', function (User $user) {
            return in_array('ROLE_GOOD_TO_REMOVE_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_GOOD_TO_REMOVE_PRINT', function (User $user) {
            return in_array('ROLE_GOOD_TO_REMOVE_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_GOOD_TO_REMOVE_VALIDATE', function (User $user) {
            return in_array('ROLE_GOOD_TO_REMOVE_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_GOOD_TO_REMOVE_REJECT', function (User $user) {
            return in_array('ROLE_GOOD_TO_REMOVE_REJECT', $user->roles) ? Response::allow() : abort(403);
        });

        // TRANSFER_DEMAND authorizations
        Gate::define('ROLE_TRANSFER_DEMAND_READ', function (User $user) {
            return in_array('ROLE_TRANSFER_DEMAND_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_DEMAND_CREATE', function (User $user) {
            return in_array('ROLE_TRANSFER_DEMAND_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_DEMAND_UPDATE', function (User $user) {
            return in_array('ROLE_TRANSFER_DEMAND_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_DEMAND_DELETE', function (User $user) {
            return in_array('ROLE_TRANSFER_DEMAND_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_DEMAND_PRINT', function (User $user) {
            return in_array('ROLE_TRANSFER_DEMAND_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_DEMAND_VALIDATE', function (User $user) {
            return in_array('ROLE_TRANSFER_DEMAND_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_DEMAND_REJECT', function (User $user) {
            return in_array('ROLE_TRANSFER_DEMAND_REJECT', $user->roles) ? Response::allow() : abort(403);
        });

        // TRANSFER authorizations
        Gate::define('ROLE_TRANSFER_READ', function (User $user) {
            return in_array('ROLE_TRANSFER_READ', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_CREATE', function (User $user) {
            return in_array('ROLE_TRANSFER_CREATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_UPDATE', function (User $user) {
            return in_array('ROLE_TRANSFER_UPDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_DELETE', function (User $user) {
            return in_array('ROLE_TRANSFER_DELETE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_PRINT', function (User $user) {
            return in_array('ROLE_TRANSFER_PRINT', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_VALIDATE', function (User $user) {
            return in_array('ROLE_TRANSFER_VALIDATE', $user->roles) ? Response::allow() : abort(403);
        });
        Gate::define('ROLE_TRANSFER_REJECT', function (User $user) {
            return in_array('ROLE_TRANSFER_REJECT', $user->roles) ? Response::allow() : abort(403);
        });*/
    }
}
