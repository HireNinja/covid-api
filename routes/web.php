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

$router->get('/', function () use ($router) {
    return "Welcome to Covid-19 API";
});

$router->get('/test', 'TestingController@test');
$router->get('/home', 'HomeController@index');

resource($router, "case", 'API\CaseController');
resource($router, "place", 'API\PlaceController');
resource($router, "post", 'API\PostController');

function resource($router, $uri, $controller)
{
    $router->get($uri, [ 'uses' => $controller . '@index']);
    $router->post($uri, [ 'uses' => $controller . '@store']);
    $router->get($uri.'/{id}', [ 'uses' => $controller . '@show']);
    $router->put($uri.'/{id}', [ 'uses' => $controller . '@update']);
    $router->patch($uri.'/{id}', [ 'uses' => $controller . '@update']);
    $router->delete($uri.'/{id}', [ 'uses' => $controller . '@destroy']);
}
