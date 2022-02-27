<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterGame;
use App\Models\Personnel;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;


class GameController extends Controller
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
                'title' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $gameName = isset($requestData['title']) ? trim($requestData['title']) : NULL;
            if (!$validator->fails()) {
                $checkGame = MasterGame::where('title', $gameName)->first();
                if (!$checkGame) {
                    $insertData = array(
                        'title' => $gameName
                    );
                    $createTeam = MasterGame::create($insertData);
                    $response->code = '00';
                    $response->desc = 'Create Master Game Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'Game Already Exist.';
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
                'image_id' => 'required|string',
                'title' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $image_id = isset($requestData['image_id']) ? trim($requestData['image_id']) : NULL;
            $title = isset($requestData['title']) ? trim($requestData['title']) : NULL;
            if (!$validator->fails()) {
                $checkGame = MasterGame::where('id', $image_id)->first();
                if ($checkGame) {
                    $updateData = array(
                        'title' => $title
                    );
                    MasterGame::where('id', $image_id)->update($updateData);
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

    public function getList(Request $request)
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
                $query = MasterGame::select('*');
                if ($search) {
                    $query->where('title', 'like', $search . '%');
                }
                $execQuery = $query->offset($offset)
                    ->limit($limit)
                    ->get();
                if ($execQuery->first()) {
                    $result = array();
                    foreach ($execQuery->toArray() as $execQuery_row) {
                        $execQuery_row['image'] = "https://vidiwijaya.my.id/assets/storage_api_mabar/upload/master_game/" . $execQuery_row['id'] . "/" . $execQuery_row['image'];
                        array_push($result, $execQuery_row);
                    }
                    $response->code = '00';
                    $response->desc = 'Get List Master Game Success!';
                    $response->data = $result;
                } else {
                    $response->code = '02';
                    $response->desc = 'List Master Game is Empty.';
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
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $gameId = isset($requestData['game_id']) ? trim($requestData['game_id']) : NULL;
            if (!$validator->fails()) {
                if ($request->hasFile('image_file')) {
                    $file = $request->file('image_file');
                    $fileExtension = $file->getClientOriginalExtension();
                    $filenameQuestion = 'master_game_' . $gameId . '.jpg';
                    $destinationPath = 'app/public/upload/master_game/' . $gameId;
                    if (!file_exists(storage_path($destinationPath))) {
                        mkdir(storage_path($destinationPath), 0775, true);
                    }
                    $request->file('image_file')->move(storage_path($destinationPath . '/'), $filenameQuestion);
                    MasterGame::where('id', $gameId)
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
