<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use App\Models\MasterGame;
use Illuminate\Http\Request;
use App\Models\MasterTeam;
use App\Models\Personnel;
use App\Models\MasterReqJoinTeam;
use App\Models\MasterTournament;
use App\Models\TeamTournament;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;

class TeamController extends Controller
{
    public function __construct(Request $request)
    {
        $token = $request->bearerToken();
        if ($token != env('GOD_BEARER')) {
            $this->middleware('auth:api');
        }
    }

    public function create(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'name' => 'required|string',
                'info' => 'required|string',
                'personnel' => 'required|string',
                'game_id' => 'required|numeric'
            ]);
            if (!$validator->fails()) {
                $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $teamName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
                $teamInfo = isset($requestData['info']) ? trim($requestData['info']) : NULL;
                $teamPersonnel = isset($requestData['personnel']) ? json_decode($requestData['personnel']) : NULL;
                $gameId = isset($requestData['game_id']) ? trim($requestData['game_id']) : NULL;

                $checkPersonnelRole = Personnel::where('user_id', $userId)
                    ->where('role', '2')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTeamName = MasterTeam::where('name', $teamName)->first();
                    if (!$checkTeamName) {
                        $checkGame = MasterGame::where("id", $gameId)->first();
                        if ($checkGame) {
                            array_push($teamPersonnel, $userId);
                            $insertData = array(
                                'name' => $teamName,
                                'info' => $teamInfo,
                                'admin_id' => $userId,
                                'personnel' => json_encode($teamPersonnel),
                                'game_id' => $gameId,
                            );
                            $createTeam = MasterTeam::create($insertData);
                            $updatePersonnelTeam = array(
                                'team_id' => $createTeam->id
                            );
                            Personnel::where('user_id', $userId)
                                ->update($updatePersonnelTeam);
                            $data = new stdClass();
                            $data->team_id = $createTeam->id;

                            $response->code = '00';
                            $response->desc = 'Create Team Success!';
                            $response->data = $data;
                        } else {
                            $response->code = '02';
                            $response->desc = 'Game Not Found.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Team Name Already Exist.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'You not have access.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function update(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'team_id' => 'required|string',
                'name' => 'required|string',
                'info' => 'required|string',
                'personnel' => 'required|string',
                'game_id' => 'required|numeric'
            ]);
            if (!$validator->fails()) {
                $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
                $teamName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
                $teamInfo = isset($requestData['info']) ? trim($requestData['info']) : NULL;
                $teamPersonnel = isset($requestData['personnel']) ? json_decode($requestData['personnel']) : NULL;
                $gameId = isset($requestData['game_id']) ? trim($requestData['game_id']) : NULL;

                $checkPersonnelRole = Personnel::where('user_id', $userId)
                    ->where('role', '2')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTeamExist = MasterTeam::where('id', $teamId)->first();
                    if ($checkTeamExist) {
                        $checkGame = MasterGame::where("id", $gameId)->first();
                        if ($checkGame) {
                            array_push($teamPersonnel, $userId);
                            $updateData = array(
                                'name' => $teamName,
                                'info' => $teamInfo,
                                'admin_id' => $userId,
                                'personnel' => json_encode($teamPersonnel),
                                'game_id' => $gameId,
                            );
                            MasterTeam::where('id', $teamId)->update($updateData);
                            $updatePersonnelTeam = array(
                                'team_id' => $teamId
                            );
                            Personnel::whereIn('user_id', $teamPersonnel)->update($updatePersonnelTeam);

                            $response->code = '00';
                            $response->desc = 'Update Team Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = 'Game Not Found.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Team Not Found.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'You not have access.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function delete(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'team_id' => 'required|string',
            ]);
            if (!$validator->fails()) {
                $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;

                $checkPersonnelRole = Personnel::where('user_id', $userId)
                    ->where('role', '2')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTeamExist = MasterTeam::where('id', $teamId)->first();
                    if ($checkTeamExist) {
                        MasterTeam::where('id', $teamId)->delete();
                        $updatePersonnelTeam = array(
                            'team_id' => NULL
                        );
                        Personnel::where('team_id', $teamId)
                            ->update($updatePersonnelTeam);
                        $response->code = '00';
                        $response->desc = 'Delete Team Success!';
                    } else {
                        $response->code = '02';
                        $response->desc = 'Team Not Found.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'You not have access.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getListTeam(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'search' => 'string',
                'page' => 'numeric'
            ]);
            if (!$validator->fails()) {
                $search = trim($requestData['search']);
                $page = !empty($requestData['page']) ? trim($requestData['page']) : 1;
                $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;

                $limit = 20;
                $query = MasterTeam::select('*');
                if ($search) {
                    $query->where('name', 'like', $search . '%');
                }
                if ($page > 1) {
                    $offset = ($page - 1) * $limit;
                    $query->offset($offset);
                }
                $execQuery = $query->limit($limit)
                    ->get();
                if ($execQuery->first()) {
                    $result = array();
                    foreach ($execQuery as $execQuery_row) {
                        $responseData = new stdClass();
                        $responseData->id = isset($execQuery_row->id) && $execQuery_row->id ? trim($execQuery_row->id) : "";
                        $responseData->name = isset($execQuery_row->name) && $execQuery_row->name ? trim($execQuery_row->name) : "";
                        $responseData->info = isset($execQuery_row->info) && $execQuery_row->info ? trim($execQuery_row->info) : "";
                        $responseData->admin_id = isset($execQuery_row->admin_id) && $execQuery_row->admin_id ? trim($execQuery_row->admin_id) : "";
                        $responseData->username_admin = "";
                        $getTeamLeadUsername = User::where('id', $execQuery_row->admin_id)->first();
                        if ($getTeamLeadUsername) {
                            $responseData->username_admin = isset($getTeamLeadUsername->username) && $getTeamLeadUsername->username ? trim($getTeamLeadUsername->username) : "";
                        }
                        // $image = URL::to("/storage_api_mabar/upload/tournament/" . $execQuery_row->id . "/" . $execQuery_row->image);
                        $responseData->image = isset($execQuery_row->image) && $execQuery_row->image ? URL::to("/storage_api_mabar/upload/team/" . $execQuery_row['id'] . "/" . $execQuery_row['image']) : NULL;
                        $responseData->image = isset($execQuery_row->image) && $execQuery_row->image ? URL::to("/storage_api_mabar/upload/team/" . $execQuery_row['id'] . "/" . $execQuery_row['image']) : NULL;
                        $responseData->personnel = array();
                        $arrayPersonnelId = json_decode($execQuery_row->personnel, true);
                        if (count($arrayPersonnelId) > 0) {
                            $getPersonnelUsername = User::whereIn('id', $arrayPersonnelId)->get();
                            foreach ($getPersonnelUsername as $rowPersonnelUsername) {
                                $arrayPersonnel = array(
                                    "user_id" => isset($rowPersonnelUsername->id) && $rowPersonnelUsername->id ? trim($rowPersonnelUsername->id) : "",
                                    "username" => isset($rowPersonnelUsername->username) && $rowPersonnelUsername->username ? trim($rowPersonnelUsername->username) : ""
                                );
                                array_push($responseData->personnel, $arrayPersonnel);
                            }
                        }
                        $responseData->game_id = isset($execQuery_row->game_id) && $execQuery_row->game_id ? trim($execQuery_row->game_id) : "";
                        $responseData->title_game = "";
                        $getGameName = MasterGame::where('id', $execQuery_row->game_id)->first();
                        if ($getGameName) {
                            $responseData->title_game = isset($getGameName->title) && $getGameName->title ? trim($getGameName->title) : "";
                        }
                        array_push($result, $responseData);
                    }

                    $response->code = '00';
                    $response->desc = 'Get List Team Success!';
                    $response->data = $result;
                } else {
                    $response->code = '02';
                    $response->desc = 'List Team is Empty.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getInfoTeam(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'team_id' => 'required|string',
            ]);
            if (!$validator->fails()) {
                $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;

                $getInfoTeam = MasterTeam::where('id', $teamId)->first();
                if ($getInfoTeam) {
                    $responseData = new stdClass();
                    $responseData->id = isset($getInfoTeam->id) && $getInfoTeam->id ? trim($getInfoTeam->id) : "";
                    $responseData->name = isset($getInfoTeam->name) && $getInfoTeam->name ? trim($getInfoTeam->name) : "";
                    $responseData->info = isset($getInfoTeam->info) && $getInfoTeam->info ? trim($getInfoTeam->info) : "";
                    $responseData->admin_id = isset($getInfoTeam->admin_id) && $getInfoTeam->admin_id ? trim($getInfoTeam->admin_id) : "";
                    $responseData->username_admin = "";
                    $getTeamLeadUsername = User::where('id', $getInfoTeam->admin_id)->first();
                    if ($getTeamLeadUsername) {
                        $responseData->username_admin = isset($getTeamLeadUsername->username) && $getTeamLeadUsername->username ? trim($getTeamLeadUsername->username) : "";
                    }
                    $responseData->admin_id = isset($getInfoTeam->admin_id) && $getInfoTeam->admin_id ? trim($getInfoTeam->admin_id) : "";
                    // $responseData->image = isset($getInfoTeam->image) && $getInfoTeam->image ? URL::to("/image/masterTeam/" . $getInfoTeam['id'] . "/" . $getInfoTeam['image']) : NULL;
                    $responseData->image = isset($getInfoTeam->image) && $getInfoTeam->image ? URL::to("/storage_api_mabar/upload/master_game/" . $getInfoTeam['id'] . "/" . $getInfoTeam['image']) : NULL;
                    $responseData->personnel = array();
                    $arrayPersonnelId = json_decode($getInfoTeam->personnel, true);
                    if (count($arrayPersonnelId) > 0) {
                        $getPersonnelUsername = User::whereIn('id', $arrayPersonnelId)->get();
                        foreach ($getPersonnelUsername as $rowPersonnelUsername) {
                            $arrayPersonnel = array(
                                "user_id" => isset($rowPersonnelUsername->id) && $rowPersonnelUsername->id ? trim($rowPersonnelUsername->id) : "",
                                "username" => isset($rowPersonnelUsername->username) && $rowPersonnelUsername->username ? trim($rowPersonnelUsername->username) : ""
                            );
                            array_push($responseData->personnel, $arrayPersonnel);
                        }
                    }
                    $responseData->game_id = isset($getInfoTeam->game_id) && $getInfoTeam->game_id ? trim($getInfoTeam->game_id) : "";
                    $responseData->title_game = "";
                    $getGameName = MasterGame::where('id', $getInfoTeam->game_id)->first();
                    if ($getGameName) {
                        $responseData->title_game = isset($getGameName->title) && $getGameName->title ? trim($getGameName->title) : "";
                    }

                    $response->code = '00';
                    $response->desc = 'Get Info Team Success!';
                    $response->data = $responseData;
                } else {
                    $response->code = '02';
                    $response->desc = 'Team Not Found.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function answerReqJoinTeam(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
                'user_id_requested' => 'required|numeric',
                'answer' => 'required|numeric',
            ]);
            if (!$validator->fails()) {
                $userId = trim($requestData['user_id']);
                $user_id_requested = trim($requestData['user_id_requested']);
                $answer = trim($requestData['answer']);

                $checkTeamExist = MasterTeam::where('admin_id', $userId)->first();
                if ($checkTeamExist) {
                    $checkPersonnelTeam = Personnel::where('user_id', $user_id_requested)
                        ->first();
                    if ($checkPersonnelTeam) {
                        $checkMasterReqJoinTeam = MasterReqJoinTeam::where('user_id', $user_id_requested)
                            ->where('team_id', $checkTeamExist->id)->first();
                        if ($checkMasterReqJoinTeam) {
                            if (empty($checkPersonnelTeam->team_id)) {
                                if ($answer == 1) {
                                    $arrayPersonnelTeam = array();
                                    $arrayPersonnelTeam = json_decode($checkTeamExist['personnel'], true);
                                    array_push($arrayPersonnelTeam, $user_id_requested);
                                    MasterTeam::where('id', $checkTeamExist['id'])
                                        ->update(array(
                                            'personnel' => json_encode($arrayPersonnelTeam)
                                        ));
                                    Personnel::where('user_id', $user_id_requested)
                                        ->update(array(
                                            'team_id' => $checkTeamExist['id']
                                        ));
                                    // MasterReqJoinTeam::where('user_id', $user_id_requested)
                                    //     ->update(array('answer' => 1));
                                    MasterReqJoinTeam::where('user_id', $user_id_requested)->delete();
                                } else {
                                    MasterReqJoinTeam::where('user_id', $user_id_requested)
                                        ->update(array('answer' => 0));
                                }
                                $response->code = '00';
                                $response->desc = 'Answer Request Success!';
                            } else {
                                $response->code = '02';
                                $response->desc = 'Personnel Request Have Team.';
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = 'Personnel Not Request a Team.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Personnel Request Not Found.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Team Not Found.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getListReqJoinTeam(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
                'team_id' => 'required|numeric',
            ]);
            if (!$validator->fails()) {
                $userId = trim($requestData['user_id']);
                $teamId = trim($requestData['team_id']);
                $checkTeamExist = MasterTeam::where('id', $teamId)
                    ->first();
                if ($checkTeamExist) {
                    if ($checkTeamExist->admin_id == $userId) {
                        $listReqJoinTeam = MasterReqJoinTeam::where('team_id', $teamId)
                            ->get();
                        if ($listReqJoinTeam->first()) {
                            $resultData = array();
                            foreach ($listReqJoinTeam->toArray() as $listReqJoinTeamRow) {
                                $personnel_name = '';
                                $personnelInfo = Personnel::select('users.username', 'personnel.*')
                                    ->leftJoin('users', 'users.id', '=', 'personnel.user_id')
                                    ->where('personnel.user_id', $listReqJoinTeamRow['user_id'])
                                    ->first();
                                if ($personnelInfo) {
                                    $personnel_name = isset($personnelInfo->firstname) && $personnelInfo->firstname ? trim($personnelInfo->firstname . ' ' . $personnelInfo->lastname) : '';
                                    $personnel_username = isset($personnelInfo->username) && $personnelInfo->username ? trim($personnelInfo->username) : '';
                                }
                                $data = array(
                                    "user_request_id" => $listReqJoinTeamRow['user_id'],
                                    "user_request_name" => $personnel_name,
                                    "user_request_username" => $personnel_username,
                                );
                                array_push($resultData, $data);
                            }
                            $response->code = '00';
                            $response->desc = 'Get List Success!';
                            $response->data = $resultData;
                        } else {
                            $response->code = '02';
                            $response->desc = 'Request List Team Empty.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Access Forbidden. Team Leader Only.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Team Not Found.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getListTournamentTeam(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
                'team_id' => 'required|numeric',
            ]);
            if (!$validator->fails()) {
                $userId = trim($requestData['user_id']);
                $teamId = trim($requestData['team_id']);

                $checkTeamPersonnel = Personnel::where('user_id', $userId)
                    ->first();
                if ($checkTeamPersonnel) {
                    if ($checkTeamPersonnel->team_id == $teamId) {
                        $checkTeamTournament = TeamTournament::where('team_id', $teamId)
                            ->where('active', '1')
                            ->get();
                        if ($checkTeamTournament->first()) {
                            $resultData = array();
                            foreach ($checkTeamTournament as $teamTournamentRow) {
                                $tournamentInfo = MasterTournament::where('id', $teamTournamentRow['tournament_id'])
                                    ->whereRaw('DATE(start_date) >= DATE(NOW()) <= DATE(end_date)')
                                    ->first();
                                if ($tournamentInfo) {
                                    $data = array(
                                        'tournament_id' => isset($teamTournamentRow->tournament_id) && $teamTournamentRow->tournament_id ? $teamTournamentRow->tournament_id : '',
                                        'tournament_name' => isset($tournamentInfo->name) && $tournamentInfo->name ? $tournamentInfo->name : '',
                                        'tournament_detail' => isset($tournamentInfo->detail) && $tournamentInfo->detail ? $tournamentInfo->detail : '',
                                        'tournament_prize' => isset($tournamentInfo->prize) && $tournamentInfo->prize ? $tournamentInfo->prize : '',
                                        // 'tournament_image' => isset($tournamentInfo->image) && $tournamentInfo->image ? URL::to("/image/masterTournament/" . $tournamentInfo->id . "/" . $tournamentInfo->image) : '',
                                        'tournament_image' => isset($tournamentInfo->image) && $tournamentInfo->image ? URL::to("/storage_api_mabar/upload/tournament/" . $tournamentInfo->id . "/" . $tournamentInfo->image) : '',
                                        'tournament_start_date' => isset($tournamentInfo->start_date) && $tournamentInfo->start_date ? date("d-m-Y", strtotime($tournamentInfo->start_date)) : '',
                                        'tournament_end_date' => isset($tournamentInfo->end_date) && $tournamentInfo->end_date ? date("d-m-Y", strtotime($tournamentInfo->end_date)) : '',
                                        'tournament_last_position' => isset($teamTournamentRow->last_position) && $teamTournamentRow->last_position ? $teamTournamentRow->last_position : '',
                                    );
                                    array_push($resultData, $data);
                                }
                            }
                            $response->code = '00';
                            $response->desc = 'Get List Tournament Team Success.';
                            $response->data = $resultData;
                        } else {
                            $response->code = '02';
                            $response->desc = 'Team Not Participate Tournament.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Personnel Team Not Match.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function uploadImage(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        DB::beginTransaction();
        try {
            $requestData = $request->all();
            $validator = Validator::make($requestData, [
                'image_file'  => 'mimes:jpeg,jpg,png,gif|required|max:1024',
                'team_id' => 'required|numeric',
                'user_id' => 'required|numeric'
            ]);
            if (!$validator->fails()) {
                $user_id = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $team_id = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;

                $checkUserHasTeam = Personnel::select('team_id')
                    ->where('user_id', $user_id)
                    ->whereNotNull('team_id')
                    ->first();
                if ($checkUserHasTeam) {
                    $checkTeam = MasterTeam::select('id', 'admin_id')
                        ->where('id', $team_id)
                        ->first();
                    if ($checkTeam) {
                        if ($checkTeam->admin_id == $user_id) {
                            if ($request->hasFile('image_file')) {
                                $file = $request->file('image_file');
                                $filenameQuestion = 'image_team_' . $checkUserHasTeam->team_id . '.jpg';
                                $destinationPath = 'app/public/upload/team/' . $checkUserHasTeam->team_id;
                                if (!file_exists(storage_path($destinationPath))) {
                                    mkdir(storage_path($destinationPath), 0775, true);
                                }
                                $request->file('image_file')->move(storage_path($destinationPath . '/'), $filenameQuestion);
                                MasterTeam::where('id', $checkUserHasTeam->team_id)
                                    ->update([
                                        "image" => $filenameQuestion
                                    ]);
                                $response->code = '00';
                                $response->desc = 'Upload Success.';
                            } else {
                                $response->code = '02';
                                $response->desc = 'Has no File Uploaded.';
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = 'You Not Leader Of Team.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Team Not Found.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'User Not Join a Team.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getListMyTeamTournament(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'team_id' => 'required|string',
                'search' => 'string',
                'page' => 'numeric'
            ]);
            if (!$validator->fails()) {
                $search = trim($requestData['search']);
                $page = !empty($requestData['page']) ? trim($requestData['page']) : 1;
                $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;

                $checkMasterTeam = MasterTeam::where('id', $teamId)
                    ->first();
                if ($checkMasterTeam) {
                    $teamMember = json_decode($checkMasterTeam->personnel, true);
                    if (in_array($userId, $teamMember)) {
                        $queryTeamTournament = TeamTournament::where('team_id', $teamId)
                            ->where('active', '1')
                            ->pluck('tournament_id');
                        if ($queryTeamTournament->first()) {
                            $arrayTournamentId = array();
                            foreach ($queryTeamTournament as $rowTournamentId) {
                                array_push($arrayTournamentId, $rowTournamentId);
                            }
                            $query = MasterTournament::whereIn('id', $arrayTournamentId);
                            $limit = 20;
                            if ($search) {
                                $query->where('name', 'like', $search . '%');
                            }
                            if ($page > 1) {
                                $offset = ($page - 1) * $limit;
                                $query->offset($offset);
                            }
                            $execQuery = $query->limit($limit)
                                ->get();
                            if ($execQuery->first()) {
                                $result = array();
                                foreach ($execQuery->toArray() as $execQuery_row) {
                                    if ($execQuery_row['image']) {
                                        // $execQuery_row['image'] = URL::to("/image/masterTeam/" . $execQuery_row['id'] . "/" . $execQuery_row['image']);
                                        $execQuery_row['image'] = URL::to("/storage_api_mabar/upload/master_game/" . $execQuery_row->id . "/" . $execQuery_row->image);
                                    }
                                    array_push($result, $execQuery_row);
                                }
                                $response->code = '00';
                                $response->desc = 'Get List My Team Tournament Success!';
                                $response->data = $result;
                            } else {
                                $response->code = '02';
                                $response->desc = 'List My Team Tournament is Empty.';
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = 'Team Not Participate In Any Tournament.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Personnel is not the Team Member.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Team Not Found.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }
}
