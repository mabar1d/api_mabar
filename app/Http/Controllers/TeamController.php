<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterTeam;
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
        try {;
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'name' => 'required|string',
                'info' => 'required|string',
                'personnel' => 'required|string',
            ]);
            $adminId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $teamName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            $teamInfo = isset($requestData['info']) ? trim($requestData['info']) : NULL;
            $teamPersonnel = isset($requestData['personnel']) ? json_decode($requestData['personnel']) : NULL;
            if (!$validator->fails()) {
                $checkTeamName = MasterTeam::where('name', $teamName)->first();
                if (!$checkTeamName) {
                    $insertData = array(
                        'name' => $teamName,
                        'info' => $teamInfo,
                        'admin_id' => $adminId,
                        'personnel' => json_encode($teamPersonnel),
                    );
                    MasterTeam::create($insertData);
                    $response->code = '00';
                    $response->desc = 'Create Team Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'Team Name Already Exist.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
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
        try {;
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
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
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
        try {;
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'team_id' => 'required|string',
            ]);
            $adminId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
            if (!$validator->fails()) {
                $checkTeamExist = MasterTeam::where('id', $teamId)->first();
                if ($checkTeamExist) {
                    MasterTeam::where('id', $teamId)->delete();
                    $response->code = '00';
                    $response->desc = 'Delete Team Success!';
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
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
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
        try {;
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            if (!$validator->fails()) {
                $getListTeam = MasterTeam::get();
                if ($getListTeam) {
                    $response->code = '00';
                    $response->desc = 'Get List Team Success!';
                    $response->data = $getListTeam;
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
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
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
        try {;
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
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
        }
        return response()->json($response);
    }
}
