<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterTeam;
use App\Models\Personnel;
use App\Models\MasterReqJoinTeam;
use App\Models\MasterTournament;
use App\Models\TeamTournament;
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
                'personnel' => 'required|string'
            ]);
            if (!$validator->fails()) {
                $adminId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $teamName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
                $teamInfo = isset($requestData['info']) ? trim($requestData['info']) : NULL;
                $teamPersonnel = isset($requestData['personnel']) ? json_decode($requestData['personnel']) : NULL;

                $checkPersonnelRole = Personnel::where('user_id', $adminId)
                    ->where('role', '2')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTeamName = MasterTeam::where('name', $teamName)->first();
                    if (!$checkTeamName) {
                        $insertData = array(
                            'name' => $teamName,
                            'info' => $teamInfo,
                            'admin_id' => $adminId,
                            'personnel' => json_encode($teamPersonnel),
                        );
                        $createTeam = MasterTeam::create($insertData);
                        $updatePersonnelTeam = array(
                            'team_id' => $createTeam->id
                        );
                        Personnel::where('user_id', $adminId)
                            ->update($updatePersonnelTeam);
                        $response->code = '00';
                        $response->desc = 'Create Team Success!';
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
            ]);
            if (!$validator->fails()) {
                $adminId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
                $teamName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
                $teamInfo = isset($requestData['info']) ? trim($requestData['info']) : NULL;
                $teamPersonnel = isset($requestData['personnel']) ? json_decode($requestData['personnel']) : NULL;

                $checkPersonnelRole = Personnel::where('user_id', $adminId)
                    ->where('role', '2')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTeamExist = MasterTeam::where('id', $teamId)->first();
                    if ($checkTeamExist) {
                        $updateData = array(
                            'name' => $teamName,
                            'info' => $teamInfo,
                            'admin_id' => $adminId,
                            'personnel' => json_encode($teamPersonnel),
                        );
                        MasterTeam::where('id', $teamId)->update($updateData);
                        $response->code = '00';
                        $response->desc = 'Update Team Success!';
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
                $adminId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;

                $checkPersonnelRole = Personnel::where('user_id', $adminId)
                    ->where('role', '2')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTeamExist = MasterTeam::where('id', $teamId)->first();
                    if ($checkTeamExist) {
                        MasterTeam::where('id', $teamId)->delete();
                        $updatePersonnelTeam = array(
                            'team_id' => NULL
                        );
                        Personnel::where('user_id', $adminId)
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
                    foreach ($execQuery->toArray() as $execQuery_row) {
                        if ($execQuery_row['image']) {
                            $execQuery_row['image'] = URL::to("/image/masterTeam/" . $execQuery_row['id'] . "/" . $execQuery_row['image']);
                        }
                        array_push($result, $execQuery_row);
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
                    if ($getInfoTeam->image) {
                        $getInfoTeam->image = URL::to("/image/masterTeam/" . $getInfoTeam['id'] . "/" . $getInfoTeam['image']);
                    }
                    $response->code = '00';
                    $response->desc = 'Get Info Team Success!';
                    $response->data = $getInfoTeam;
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
                $adminId = trim($requestData['user_id']);
                $user_id_requested = trim($requestData['user_id_requested']);
                $answer = trim($requestData['answer']);

                $checkTeamExist = MasterTeam::where('admin_id', $adminId)->first();
                if ($checkTeamExist) {
                    $checkPersonnelTeam = Personnel::where('user_id', $user_id_requested)
                        ->first();
                    if ($checkPersonnelTeam) {
                        if (empty($checkPersonnelTeam->team_id)) {
                            if ($answer == 1) {
                                $arrayPersonnelTeam = array();
                                if (is_array($checkTeamExist['personnel'])) {
                                    $arrayPersonnelTeam = $checkTeamExist['personnel'];
                                }
                                array_push($arrayPersonnelTeam, $user_id_requested);
                                MasterTeam::where('id', $checkTeamExist['id'])
                                    ->update(array(
                                        'personnel' => json_encode($arrayPersonnelTeam)
                                    ));
                                Personnel::where('user_id', $user_id_requested)
                                    ->update(array(
                                        'team_id' => $checkTeamExist['id']
                                    ));
                                MasterReqJoinTeam::where('user_id', $user_id_requested)->delete();
                            } else {
                                MasterReqJoinTeam::where('user_id', $user_id_requested)
                                    ->update(array('answer' => 0));
                            }
                            $response->code = '00';
                            $response->desc = 'Answer Request Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = 'Personnel Have Team.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Personnel Not Found.';
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
                    ->where('admin_id', $userId)
                    ->first()->toArray();
                if ($checkTeamExist) {
                    $listReqJoinTeam = MasterReqJoinTeam::where('team_id', $teamId)
                        ->get();
                    if ($listReqJoinTeam->first()) {
                        $resultData = array();
                        foreach ($listReqJoinTeam->toArray() as $listReqJoinTeamRow) {
                            $user_name = '';
                            $personnelInfo = Personnel::where('user_id', $listReqJoinTeamRow['user_id'])
                                ->first();
                            if ($personnelInfo) {
                                $user_name = isset($personnelInfo->firstname) && $personnelInfo->firstname ? $personnelInfo->firstname . ' ' . $personnelInfo->lastname : '';
                            }
                            $data = array(
                                "user_request_id" => $listReqJoinTeamRow['user_id'],
                                "user_request_name" => $user_name
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
                    $response->desc = 'Access Denied. Team Leader Only.';
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
                                        'tournament_image' => isset($tournamentInfo->image) && $tournamentInfo->image ? URL::to("/image/masterTournament/" . $tournamentInfo->id . "/" . $tournamentInfo->image) : '',
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
                'user_id' => 'required|numeric'
            ]);
            if (!$validator->fails()) {
                $checkHasTeam = Personnel::select('team_id')
                    ->where('user_id', $requestData['user_id'])
                    ->whereNotNull('team_id')
                    ->first()->toArray();
                if ($checkHasTeam) {
                    if ($request->hasFile('image_file')) {
                        $file = $request->file('image_file');
                        $fileExtension = $file->getClientOriginalExtension();
                        $filenameQuestion = 'image_team_' . $checkHasTeam['team_id'] . '.jpg';
                        $destinationPath = 'app/public/upload/team/' . $checkHasTeam['team_id'];
                        if (!file_exists(storage_path($destinationPath))) {
                            mkdir(storage_path($destinationPath), 0775, true);
                        }
                        $request->file('image_file')->move(storage_path($destinationPath . '/'), $filenameQuestion);
                        MasterTeam::where('id', $checkHasTeam['team_id'])
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
        return response()->json($response);
    }
}
