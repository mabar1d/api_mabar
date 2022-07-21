<?php

namespace App\Http\Controllers;

use App\Models\ApkMenu;
use App\Models\ApkMenuModel;
use App\Models\LogApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;

class ApkController extends Controller
{
    public function __construct(Request $request)
    {
        $token = $request->bearerToken();
        if ($token != env('GOD_BEARER')) {
            $this->middleware('auth:api');
        }
    }

    public function getListApkMenu(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            if (!$validator->fails()) {
                $getData = ApkMenuModel::getListApkMenu(array(
                    "status" => "1"
                ));
                if ($getData) {
                    $response->code = '00';
                    $response->desc = 'Get List Menu APK Success!';
                    $response->data = $getData;
                } else {
                    $response->code = '02';
                    $response->desc = 'List Menu APK is Empty.';
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
                        $getInfoMasterGame->image = URL::to("/storage_api_mabar/upload/master_game/" . $getInfoMasterGame->id . "/" . $getInfoMasterGame->image);
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
                'image_file'  => 'mimes:jpeg,jpg,png,gif|required|max:1024',
                'user_id' => 'required|numeric'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $gameId = isset($requestData['game_id']) ? trim($requestData['game_id']) : NULL;
            if (!$validator->fails()) {
                if ($request->hasFile('image_file')) {
                    // $file = $request->file('image_file');
                    // $fileExtension = $file->getClientOriginalExtension();
                    $filenameQuestion = 'master_game_' . $gameId . '.jpg';
                    $destinationPath = 'app/public/upload/masterGame/' . $gameId;
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
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }
}
