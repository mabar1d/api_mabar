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

$router->group(['prefix' => 'api'], function ($router) {
    $router->post('logout', 'AuthController@logout');
    $router->post('refresh', 'AuthController@refresh');
    $router->get('profile', 'AuthController@userProfile');
    $router->post('getPersonnel', 'PersonnelController@getPersonnel');
    $router->post('getListPersonnel', 'PersonnelController@getListPersonnel');
    $router->post('updateInfoPersonnel', 'PersonnelController@updateInfoPersonnel');
    $router->post('updateTeamPersonnel', 'PersonnelController@updateTeamPersonnel');
    $router->post('uploadImagePersonnel', 'PersonnelController@uploadImage');
    $router->post('personnelReqHost', 'PersonnelController@personnelReqHost');
    $router->post('personnelReqTeamLead', 'PersonnelController@personnelReqTeamLead');
    $router->post('personnelReqJoinTeam', 'PersonnelController@personnelReqJoinTeam');
    $router->post('personnelLeaveTeam', 'PersonnelController@personnelLeaveTeam');

    $router->post('createTeam', 'TeamController@create');
    $router->post('updateTeam', 'TeamController@update');
    $router->post('deleteTeam', 'TeamController@delete');
    $router->post('uploadImageTeam', 'TeamController@uploadImage');
    $router->post('getListTeam', 'TeamController@getListTeam');
    $router->post('getInfoTeam', 'TeamController@getInfoTeam');
    $router->post('answerReqJoinTeam', 'TeamController@answerReqJoinTeam');

    $router->post('createTournament', 'TournamentController@create');
    $router->post('updateTournament', 'TournamentController@update');
    $router->post('deleteTournamet', 'TournamentController@delete');
    $router->post('getListTournament', 'TournamentController@getListTournament');
    $router->post('getInfoTournament', 'TournamentController@getInfoTournament');
    $router->post('uploadImageTournament', 'TournamentController@uploadImage');

    $router->post('createGame', 'GameController@create');
    $router->post('updateMasterGame', 'GameController@update');
    $router->post('deleteMasterGame', 'GameController@delete');
    $router->post('getListMasterGame', 'GameController@getList');
    $router->post('getInfoMasterGame', 'GameController@getInfo');
    $router->post('uploadImageGame', 'GameController@uploadImage');
});

$router->get('/image/master_game/{id}/{image_id}', 'ImageController@showImageGame');

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
});
