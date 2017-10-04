<?php

namespace App\Providers;

// use App\User;
//use \TANIOS\Airtable\Airtable;
//use app\providers\AirtableServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\GenericUser;

class AuthServiceProvider extends ServiceProvider
{
    private $Airtable;
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
     * Airtable plugin to get a record
     *
     * @return void
     */
    private function getRecordById($tableName, $tableId)
    {
        /*$airtable = new Airtable([
            'api_key'=> env('API_KEY'),
            'base'   => env('BASE_KEY')
        ]);
        $response = $airtable->getContent($tableName.'/'.$tableId)->getResponse();*/
        $response = $this->Airtable->getContent($tableName.'/'.$tableId)->getResponse();
        //dd($response->id);
        return $response->id?new GenericUser(['id' =>$response->id, 'name' =>$response->fields->User]):null;
    }
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    // public function boot(Airtable $Airtable)
    public function boot()
    {
        //$this->Airtable = $Airtable;
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            return new GenericUser(['id' =>1, 'name' => 'ger']);
            //dd($request->headers->all() );
            if ($request->header('api-token')) {
                //return User::where('api_token', $request->input('api_token'))->first();
                return $this->getRecordById('Admins', $request->header('api-token'));
            } else {
                die("no token");
            }
        });
    }
}
