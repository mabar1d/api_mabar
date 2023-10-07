<?php

namespace App\Http\Controllers;

use App\Helpers\FcmFirebase;
use App\Models\ApkMenu;
use App\Models\ApkMenuModel;
use App\Models\ApkVersionModel;
use App\Models\JobNotifFirebaseModel;
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
            $this->middleware('auth:api', ['except' => ['sendNotification', 'checkVersionApk', 'getListApkMenu']]);
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

    public function checkVersionApk(Request $request)
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
                $getData = ApkVersionModel::getList();
                $getDataVersion = array();
                foreach ($getData as $rowData) {
                    if ($rowData["type"] == "apk") {
                        $getDataVersion["version_apk"] = $rowData["version"];
                    } else if ($rowData["type"] == "database") {
                        $getDataVersion["version_database"][] = array(
                            "id" => $rowData["id"],
                            "type" => $rowData["name"],
                            "version" => $rowData["version"]
                        );
                    }
                }
                if ($getDataVersion) {
                    $response->code = '00';
                    $response->desc = 'Get List APK Version Success!';
                    $response->data = $getDataVersion;
                } else {
                    $response->code = '02';
                    $response->desc = 'List APK Version is Empty.';
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

    public function sendNotification()
    {
        DB::beginTransaction();
        try {
            $count = 0;
            $getListNotif = JobNotifFirebaseModel::getList(array(
                "status" => 0,
                "limit" => 1000
            ));
            foreach ($getListNotif as $rowListNotif) {
                $keyClient = $rowListNotif["client_key"];
                $titleFirebase = $rowListNotif["notif_title"];
                $bodyFirebase = $rowListNotif["notif_body"];
                $imgFirebase = $rowListNotif["notif_img_url"];
                $urlFirebase = $rowListNotif["notif_url"];
                $send = FcmFirebase::send($keyClient, $titleFirebase, $bodyFirebase, $imgFirebase, $urlFirebase);
                if ($send->success == 1) {
                    JobNotifFirebaseModel::find($rowListNotif["id"])->update([
                        "status" => 1
                    ]);
                    $count++;
                } else {
                    JobNotifFirebaseModel::find($rowListNotif["id"])->update([
                        "status" => 2
                    ]);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }
}
