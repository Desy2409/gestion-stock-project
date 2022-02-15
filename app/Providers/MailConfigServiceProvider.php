<?php

namespace App\Providers;

use App\Models\Driver;
use App\Models\EmailChannelParams;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
      
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    //     $emailChannelParams = EmailChannelParams::where('is_active', 1)->first();
    // //    dd($emailChannelParams);
    //     $driver = $emailChannelParams ? $emailChannelParams->driver : null;
    //     $config = [];

    //     if ($emailChannelParams !== null && $driver !== null) {
    //         switch (strtolower($driver->code)) {
    //             case 'smtp':
    //                 $config = [
    //                     'transport' => strtolower($driver->code),
    //                     'host' => strtolower($emailChannelParams->server_name),
    //                     'port' => $emailChannelParams->port,
    //                     'encryption' => strtolower($emailChannelParams->encryption),
    //                     'username' => $emailChannelParams->username,
    //                     'password' => $emailChannelParams->password,
    //                     'from' => array('address' => $emailChannelParams->from_address, 'name' => $emailChannelParams->from_name),
    //                     'timeout' => null,
    //                     'auth_mode' => null,
    //                 ];
    //                 break;

    //             case 'ses':
    //                 $config = [
    //                     'transport' => strtolower($driver->code),
    //                     'from' => array('address' => $emailChannelParams->from_address, 'name' => $emailChannelParams->from_name),
    //                 ];
    //                 break;

    //             case 'mailgun':
    //                 $config = [
    //                     'transport' => strtolower($driver->code),
    //                     'from' => array('address' => $emailChannelParams->from_address, 'name' => $emailChannelParams->from_name),
    //                 ];
    //                 break;

    //             case 'postmark':
    //                 $config = [
    //                     'transport' => strtolower($driver->code),
    //                     'from' => array('address' => $emailChannelParams->from_address, 'name' => $emailChannelParams->from_name),
    //                 ];
    //                 break;

    //             case 'sendmail':
    //                 $config = [
    //                     'transport' => strtolower($driver->code),
    //                     'path' => '/usr/sbin/sendmail -bs',
    //                     'from' => array('address' => $emailChannelParams->from_address, 'name' => $emailChannelParams->from_name),
    //                 ];
    //                 break;

    //             case 'log':
    //                 $config = [
    //                     'transport' => strtolower($driver->code),
    //                     'channel' => env('MAIL_LOG_CHANNEL'),
    //                     'from' => array('address' => $emailChannelParams->from_address, 'name' => $emailChannelParams->from_name),
    //                 ];
    //                 break;

    //             case 'array':
    //                 $config = [
    //                     'transport' => strtolower($driver->code),
    //                     'from' => array('address' => $emailChannelParams->from_address, 'name' => $emailChannelParams->from_name),
    //                 ];
    //                 break;

    //             case 'failover':
    //                 $config = [
    //                     'transport' => strtolower($driver->code),
    //                     'mailers' => [
    //                         'smtp',
    //                         'log',
    //                     ],
    //                     'from' => array('address' => $emailChannelParams->from_address, 'name' => $emailChannelParams->from_name),
    //                 ];
    //                 break;

    //             default:
    //                 $config = [
    //                     'transport' => 'smtp',
    //                     'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
    //                     'port' => env('MAIL_PORT', 587),
    //                     'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    //                     'username' => env('MAIL_USERNAME'),
    //                     'password' => env('MAIL_PASSWORD'),
    //                     'from' => array('address' => 'hello@example.com', 'name' => 'Exemple'),
    //                     'timeout' => null,
    //                     'auth_mode' => null,
    //                 ];
    //                 break;
    //         }
    //         Config::set('emailChannelParams', $config);
    //     }
    }
}
