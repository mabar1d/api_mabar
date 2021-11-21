<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterTeam;
use App\Models\Personnel;
use App\Models\MasterReqJoinTeam;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;


class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
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
            $adminId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $teamName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            $teamInfo = isset($requestData['info']) ? trim($requestData['info']) : NULL;
            $teamPersonnel = isset($requestData['personnel']) ? json_decode($requestData['personnel']) : NULL;
            if (!$validator->fails()) {
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
            $adminId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
            $teamName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            $teamInfo = isset($requestData['info']) ? trim($requestData['info']) : NULL;
            $teamPersonnel = isset($requestData['personnel']) ? json_decode($requestData['personnel']) : NULL;
            if (!$validator->fails()) {
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
            $adminId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
            if (!$validator->fails()) {
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
            $search = trim($requestData['search']);
            $page = !empty($requestData['page']) ? trim($requestData['page']) : 0;
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            if (!$validator->fails()) {
                $limit = 20;
                $offset = $page;
                $query = MasterTeam::select('*');
                if ($search) {
                    $query->where('name', 'like', $search . '%');
                }
                $execQuery = $query->offset($offset)
                    ->limit($limit)
                    ->get();
                if ($execQuery->first()) {
                    $result = array();
                    foreach ($execQuery->toArray() as $execQuery_row) {
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
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
            if (!$validator->fails()) {
                $getInfoTeam = MasterTeam::where('id', $teamId)->first();
                if ($getInfoTeam) {
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
            $adminId = trim($requestData['user_id']);
            $user_id_requested = trim($requestData['user_id_requested']);
            $answer = trim($requestData['answer']);
            if (!$validator->fails()) {
                $checkTeamExist = MasterTeam::where('admin_id', $adminId)->first();
                if ($checkTeamExist) {
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
}
