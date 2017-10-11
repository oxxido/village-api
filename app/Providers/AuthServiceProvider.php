<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    // public function boot(Airtable $Airtable)
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {

            if ($authHeader = $request->header('Authorization')) {
                //return User::where('api_token', $request->input('api_token'))->first();
                // check parsing header:
                $authHeader = str_replace("Bearer ", "", $authHeader);
                try {
                    $authInfo = Crypt::decrypt($authHeader);
                    if ($authInfo) {
                        list($spaceId, $spaceName, $gc) = explode('|', $authInfo);
                        if ($spaceId) {
                            $spaceInfo = ['id' => $spaceId, 'name' => $spaceName];
                            $request->attributes->add($spaceInfo);
                            return new GenericUser($spaceInfo);
                        } else {
                            return null;
                        }
                    }
                } catch (DecryptException $e) {
                    return null;
                }
            }
        });
    }
}
