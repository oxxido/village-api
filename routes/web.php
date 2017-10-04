<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//$router->group(['middleware'=>'auth'], function () use ($router) {
$offset = '[/{offset:[A-Za-z0-9\/]+}]';
$router->group([], function () use ($router, $offset) {
    $router->get('/', ['uses' => 'Controller@index']);
    $router->get('/people' .        $offset, ['uses' => 'Controller@people']);
    $router->get('/organizations' . $offset, ['uses' => 'Controller@organizations']);
    $router->get('/checkins' .      $offset, ['uses' => 'Controller@checkins']);
    $router->get('/plans' .         $offset, ['uses' => 'Controller@plans']);

    $router->get('/admins' .        $offset, ['uses' => 'Controller@admins']);
    $router->get('/admin' .         $offset, ['uses' => 'Controller@admin']);
    /*$router->get('user/profile', function () {
        // Uses Auth Middleware
    });*/
});