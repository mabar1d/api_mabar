<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use Illuminate\Http\Request;
use App\Models\MasterGame;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;

class GameController extends Controller
{
    public function __construct(Request $request)
    {
        $token = $request->bearerToken();
        if ($token != env('GOD_BEARER')) {
            $this->middleware('auth:api', ['except' => ['getList', 'getInfo', 'count']]);
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
                'game_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $gameId = isset($requestData['game_id']) ? trim($requestData['game_id']) : NULL;
            if (!$validator->fails()) {
                $checkMasterGameExist = MasterGame::where('id', $gameId)->first();
                if ($checkMasterGameExist) {
                    // MasterGame::where('id', $gameId)->delete();
                    $destinationPath = 'public/upload/masterGame/' . $checkMasterGameExist->image;
                    if (file_exists(base_path($destinationPath))) {
                        unlink(base_path($destinationPath));
                    }
                    $response->code = '00';
                    $response->desc = 'Delete Master Game Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'Master Game Not Found.';
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
                'page' => 'numeric',
                'order_by' => 'string',
                'order_by_method' => 'string',
                'limit' => 'numeric',
                'offset' => 'numeric',
                'status' => 'string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $search = trim($requestData['search']);
            $page = !empty($requestData['page']) && $requestData['page'] ? trim($requestData['page']) : 0;
            $orderBy = isset($requestData['order_by']) && $requestData['order_by'] ? trim($requestData['order_by']) : "created_at";
            $orderByMethod = isset($requestData['order_by_method']) && $requestData['order_by_method'] ? trim($requestData['order_by_method']) : "desc";
            $limit = isset($requestData['limit']) && $requestData['limit'] ? trim($requestData['limit']) : 20;
            $offset = isset($requestData['offset']) && $requestData['offset'] ? trim($requestData['offset']) : 0;
            $status = isset($requestData['status']) && $requestData['status'] ? trim($requestData['status']) : 1;
            if (!$validator->fails()) {
                $query = MasterGame::select('*');
                $query->where("status", $status);
                if ($search) {
                    $query->where('title', 'like', $search . '%');
                }
                if ($page > 1) {
                    $offset = ($page - 1) * $limit;
                }
                $query->orderBy($orderBy, $orderByMethod);
                $query->limit($limit);
                $query->offset($offset);
                $execQuery = $query->get();
                if ($execQuery->first()) {
                    $result = array();
                    foreach ($execQuery->toArray() as $execQuery_row) {
                        if ($execQuery_row['image']) {
                            // $execQuery_row['image'] = URL::to("/image/masterGame/" . $execQuery_row['id'] . "/" . $execQuery_row['image']);
                            $execQuery_row['image'] = URL::to("/upload/masterGame/" . $execQuery_row['image']);
                        }
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
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getInfo(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'game_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $gameId = isset($requestData['game_id']) ? trim($requestData['game_id']) : NULL;
            if (!$validator->fails()) {
                $getInfoMasterGame = MasterGame::where('id', $gameId)->first();
                if ($getInfoMasterGame) {
                    $response->code = '00';
                    $response->desc = 'Get Info Master Game Success!';
                    if ($getInfoMasterGame->image) {
                        // $getInfoMasterGame->image = URL::to("/image/masterGame/" . $getInfoMasterGame->id . "/" . $getInfoMasterGame->image);
                        $getInfoMasterGame->image = URL::to("/upload/masterGame/" . $getInfoMasterGame->image);
                    }
                    $response->data = $getInfoMasterGame;
                } else {
                    $response->code = '02';
                    $response->desc = 'Master Game Not Found.';
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
                'image_file'  => 'mimes:jpeg,jpg,png,gif|required|max:1024'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $gameId = isset($requestData['game_id']) ? trim($requestData['game_id']) : NULL;
            if (!$validator->fails()) {
                if ($request->hasFile('image_file')) {
                    $fileName = bin2hex(openssl_random_pseudo_bytes(10)) . '.jpg';
                    $destinationPath = 'public/upload/masterGame/';
                    if (!file_exists(base_path($destinationPath))) {
                        mkdir(base_path($destinationPath), 0775, true);
                    }
                    $request->file('image_file')->move(base_path($destinationPath . '/'), $fileName);
                    $response->code = '00';
                    $response->desc = 'File Has Uploaded.';
                    $response->data = [
                        'filename' => $fileName
                    ];
                } else {
                    $response->code = '02';
                    $response->desc = 'Has no File Uploaded.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function count(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'status' => 'string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $status = isset($requestData['status']) && $requestData['status'] ? trim($requestData['status']) : 1;
            if (!$validator->fails()) {
                $query = MasterGame::select('*');
                $query->where("status", $status);
                $execQuery = $query->count();

                $response->code = '00';
                $response->desc = 'Count Master Game Success!';
                $response->data = [
                    "totalCount" => $execQuery
                ];
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
