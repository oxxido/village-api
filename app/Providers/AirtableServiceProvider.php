<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \TANIOS\Airtable\Airtable;

class AirtableServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        /*$this->app->singleton(Connection::class, function ($app) {
            return new Connection(config('riak'));
        });*/
        /*$this->app->singleton('\TANIOS\Airtable\Airtable', function ($app) {
            return new Airtable([
                'api_key'=> env('API_KEY'),
                'base'   => env('BASE_KEY')
            ]);
        });*/
        /*$config = [
                'api_key'=> env('API_KEY'),
                'base'   => env('BASE_KEY')
            ];
        $airtable = new Airtable($config);*/
       /*$this->app->bind(\TANIOS\Airtable\Airtable::class, function () {
            
            return $airtable;
        });*/
        // $this->app->instance('\TANIOS\Airtable\Airtable', $airtable);
        $this->app->singleton(AirtableServiceProvider::class, function ($app) {
            return new Airtable([
                'api_key'=> env('API_KEY'),
                'base'   => env('BASE_KEY')
            ]);
        });
    }
}