<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Artisan;

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

    //PERSONNEL
    $router->post('getPersonnel', 'PersonnelController@getPersonnel');
    $router->post('getListPersonnel', 'PersonnelController@getListPersonnel');
    $router->post('updateInfoPersonnel', 'PersonnelController@updateInfoPersonnel');
    $router->post('updateTeamPersonnel', 'PersonnelController@updateTeamPersonnel');
    $router->post('uploadImagePersonnel', 'PersonnelController@uploadImage');
    $router->post('personnelReqHost', 'PersonnelController@personnelReqHost');
    $router->post('personnelReqMember', 'PersonnelController@personnelReqMember');
    $router->post('personnelReqTeamLead', 'PersonnelController@personnelReqTeamLead');
    $router->post('personnelReqJoinTeam', 'PersonnelController@personnelReqJoinTeam');
    $router->post('personnelLeaveTeam', 'PersonnelController@personnelLeaveTeam');
    $router->post('getPersonnelNotMember', 'PersonnelController@getPersonnelNotMember');

    //TEAM
    $router->post('createTeam', 'TeamController@create');
    $router->post('updateTeam', 'TeamController@update');
    $router->post('deleteTeam', 'TeamController@delete');
    $router->post('uploadImageTeam', 'TeamController@uploadImage');
    $router->post('getListTeam', 'TeamController@getListTeam');
    $router->post('getInfoTeam', 'TeamController@getInfoTeam');
    $router->post('answerReqJoinTeam', 'TeamController@answerReqJoinTeam');
    $router->post('getListReqJoinTeam', 'TeamController@getListReqJoinTeam');
    $router->post('getListTournamentTeam', 'TeamController@getListTournamentTeam');
    $router->post('getListMyTeamTournament', 'TeamController@getListMyTeamTournament');

    //TOURNAMENT
    $router->post('createTournament', 'TournamentController@create');
    $router->post('updateTournament', 'TournamentController@update');
    $router->post('deleteTournamet', 'TournamentController@delete');
    $router->post('registerTournament', 'TournamentController@registerTournament');
    $router->post('getListTournament', 'TournamentController@getListTournament');
    $router->post('getInfoTournament', 'TournamentController@getInfoTournament');
    $router->post('uploadImageTournament', 'TournamentController@uploadImage');
    $router->post('getCarouselTournament', 'TournamentController@getCarouselTournament');
    $router->post('getListMyTournament', 'TournamentController@getListMyTournament');
    //TOURNAMENT TREE
    $router->post('getTournamentTreeWeb', 'TournamentController@getTournamentTreeWeb');
    $router->post('setMatchTournamentTree', 'TournamentController@setMatchTournamentTree');
    $router->post('updateScoreTournamentTree', 'TournamentController@updateScoreTournamentTree');
    $router->post('randomMatchTournamentTree', 'TournamentController@randomMatchTournamentTree');
    $router->post('getListMatchTournamentTree', 'TournamentController@getListMatchTournamentTree');
    // TOURNAMENT GROUP
    // $router->post('getListMatchTournamentStanding', 'TournamentController@getListMatchTournamentStanding');
    // $router->post('setMatchTournamentStanding', 'TournamentController@setMatchTournamentStanding');
    $router->post('randomGroupTournamentStanding', 'TournamentController@randomGroupTournamentStanding');
    $router->post('setGroupTournamentStanding', 'TournamentController@setGroupTournamentStanding');
    $router->post('getInfoTournamentStanding', 'TournamentController@getInfoTournamentStanding');
    $router->post('getInfoTeamTournamentStanding', 'TournamentController@getInfoTeamTournamentStanding');


    //RATING TOURNAMENT
    $router->post('giveRatingTournament', 'RatingTournamentController@create');
    $router->post('updateRatingTournament', 'RatingTournamentController@update');
    $router->post('deleteRatingTournamet', 'RatingTournamentController@delete');

    //GAME
    $router->post('createGame', 'GameController@create');
    $router->post('updateMasterGame', 'GameController@update');
    $router->post('deleteMasterGame', 'GameController@delete');
    $router->post('getListMasterGame', 'GameController@getList');
    $router->post('getInfoMasterGame', 'GameController@getInfo');
    $router->post('uploadImageGame', 'GameController@uploadImage');

    //NEWS CATEGORY
    $router->post('createNewsCategory', 'NewsCategoryController@create');
    $router->post('updateNewsCategory', 'NewsCategoryController@update');
    $router->post('deleteNewsCategory', 'NewsCategoryController@delete');
    $router->post('getListNewsCategory', 'NewsCategoryController@getList');
    $router->post('getInfoNewsCategory', 'NewsCategoryController@getInfo');

    //NEWS
    $router->post('createNews', 'NewsController@create');
    $router->post('updateNews', 'NewsController@update');
    $router->post('deleteNews', 'NewsController@delete');
    $router->post('getListNews', 'NewsController@getList');
    $router->post('getInfoNews', 'NewsController@getInfo');
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post('getListApkMenu', 'ApkController@getListApkMenu');
});
