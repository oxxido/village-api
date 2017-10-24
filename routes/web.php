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
$offsetRegex = '[/{offset:[A-Za-z0-9\/]+}]';
$idRegex = '[/{id:[A-Za-z0-9\/]+}]';
$router->group(['middleware'=>'auth'], function () use ($router, $offsetRegex, $idRegex) {
    $router->get('/',         ['uses' => 'Controller@index']);
    $router->get('/space',    ['uses' => 'Controller@space']);
    $router->post('/checkin', ['uses' => 'Controller@addCheckIn']);

    $router->get('/people' .        $offsetRegex, ['uses' => 'Controller@people']);
    $router->get('/organizations' . $offsetRegex, ['uses' => 'Controller@organizations']);
    $router->get('/checkins' .      $offsetRegex, ['uses' => 'Controller@checkins']);
    $router->get('/billing' .       $offsetRegex, ['uses' => 'Controller@billing']);
    $router->get('/plans' .         $offsetRegex, ['uses' => 'Controller@plans']);
    //$router->get('/admins' .      $offsetRegex, ['uses' => 'Controller@admins']);
    //$router->get('/admin' .       $offsetRegex, ['uses' => 'Controller@admin']);
    $router->get('/person' .        $idRegex,     ['uses' => 'Controller@person']);

    $router->get('/personcheckins/{name}', ['uses' => 'Controller@personCheckins']);
    $router->get('/period/{year}/{month}', ['uses' => 'Controller@period']);
});

$router->get('/spaces' . $offsetRegex, ['uses' => 'Controller@spaces']);
$router->post('/login',                ['uses' => 'Controller@login' ]);
