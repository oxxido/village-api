<?php

namespace App\Providers;

use \TANIOS\Airtable\Airtable;
use Illuminate\Support\ServiceProvider;

class AirtableServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Airtable::class, function ($app) {
            return new Airtable([
                'api_key'=> env('API_KEY'),
                'base'   => env('BASE_KEY')
            ]);
        });
    }
}
