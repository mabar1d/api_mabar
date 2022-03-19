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
    $router->post('getListReqJoinTeam', 'TeamController@getListReqJoinTeam');
    $router->post('getListTournamentTeam', 'TeamController@getListTournamentTeam');

    $router->post('createTournament', 'TournamentController@create');
    $router->post('updateTournament', 'TournamentController@update');
    $router->post('deleteTournamet', 'TournamentController@delete');
    $router->post('registerTournament', 'TournamentController@registerTournament');
    $router->post('getListTournament', 'TournamentController@getListTournament');
    $router->post('getInfoTournament', 'TournamentController@getInfoTournament');
    $router->post('uploadImageTournament', 'TournamentController@uploadImage');
    $router->post('getCarouselTournament', 'TournamentController@getCarouselTournament');

    $router->post('giveRatingTournament', 'RatingTournamentController@create');
    $router->post('updateRatingTournament', 'RatingTournamentController@update');
    $router->post('deleteRatingTournamet', 'RatingTournamentController@delete');

    $router->post('createGame', 'GameController@create');
    $router->post('updateMasterGame', 'GameController@update');
    $router->post('deleteMasterGame', 'GameController@delete');
    $router->post('getListMasterGame', 'GameController@getList');
    $router->post('getInfoMasterGame', 'GameController@getInfo');
    $router->post('uploadImageGame', 'GameController@uploadImage');
});

$router->group(['prefix' => 'image'], function ($router) {
    $router->get('personnel/{id}/{image_id}', 'ImageController@showImagePersonnel');
    $router->get('masterGame/{id}/{image_id}', 'ImageController@showImageGame');
    $router->get('masterTeam/{id}/{image_id}', 'ImageController@showImageTeam');
    $router->get('masterTournament/{id}/{image_id}', 'ImageController@showImageTournament');
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
});
