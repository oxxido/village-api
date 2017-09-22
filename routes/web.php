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

$router->group([], function () use ($router) {
    $router->get('/', ['uses' => 'Controller@index']);
    $router->get('/people', ['uses' => 'Controller@people']);
    $router->get('/organizations', ['uses' => 'Controller@organizations']);
    $router->get('/plans', ['uses' => 'Controller@plans']);
    /*$router->get('user/profile', function () {
        // Uses Auth Middleware
    });*/
});