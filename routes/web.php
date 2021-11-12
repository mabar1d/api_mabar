<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
    // return $router->app->version();
    return "Welcome To The Jungle! Let's ROCK!!!!";
});

$router->group(['middleware' => 'auth', 'prefix' => 'api'], function ($router) {
    $router->post('logout', 'AuthController@logout');
    $router->post('refresh', 'AuthController@refresh');
    $router->get('profile', 'AuthController@userProfile');
    $router->post('getPersonnel', 'PersonnelController@getPersonnel');
    $router->post('getListPersonnel', 'PersonnelController@getListPersonnel');
    $router->post('updateInfoPersonnel', 'PersonnelController@updateInfoPersonnel');
    $router->post('updateTeamPersonnel', 'PersonnelController@updateTeamPersonnel');
    $router->post('createTeam', 'TeamController@create');
    $router->post('updateTeam', 'TeamController@update');
    $router->post('deleteTeam', 'TeamController@delete');
    $router->post('getListTeam', 'TeamController@getListTeam');
    $router->post('getInfoTeam', 'TeamController@getInfoTeam');
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
});
