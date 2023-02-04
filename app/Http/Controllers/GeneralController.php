<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use Illuminate\Http\Request;
use App\Models\MasterGame;
use App\Models\GeneralModel;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;

class GeneralController extends Controller
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
                'status' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $newsCategoryName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            $newsCategoryDesc = isset($requestData['desc']) ? trim($requestData['desc']) : NULL;
            $newsCategoryStatus = isset($requestData['status']) ? trim($requestData['status']) : 0;
            if (!$validator->fails()) {
                $checkExist = NewsCategoryModel::countNewsCategory(array("name" => $newsCategoryName));
                if (!$checkExist) {
                    $insertData = array(
                        'name' => $newsCategoryName,
                        'desc' => $newsCategoryDesc,
                        'status' => $newsCategoryStatus,
                        'created_by' => $userId
                    );
                    NewsCategoryModel::create($insertData);
                    $response->code = '00';
                    $response->desc = 'Create News Category Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'News Category Already Exist.';
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
                'news_category_id' => 'required|string',
                'name' => 'required|string',
                'status' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $newsCategoryId = isset($requestData['news_category_id']) ? trim($requestData['news_category_id']) : NULL;
            $newsCategoryName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            $newsCategoryDesc = isset($requestData['desc']) ? trim($requestData['desc']) : NULL;
            $newsCategoryStatus = isset($requestData['status']) ? trim($requestData['status']) : 0;
            if (!$validator->fails()) {
                $checkExist = NewsCategoryModel::countNewsCategory(array("id" => $newsCategoryId));
                if ($checkExist) {
                    $updateData = array(
                        'name' => $newsCategoryName,
                        'desc' => $newsCategoryDesc,
                        'status' => $newsCategoryStatus,
                        'updated_by' => $userId
                    );
                    NewsCategoryModel::where('id', $newsCategoryId)->update($updateData);
                    $response->code = '00';
                    $response->desc = 'Update News Category Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'News Category Not Found.';
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
                'news_category_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $newsCategoryId = isset($requestData['news_category_id']) ? trim($requestData['news_category_id']) : NULL;
            if (!$validator->fails()) {
                $checkExist = NewsCategoryModel::countNewsCategory(array("id" => $newsCategoryId));
                if ($checkExist) {
                    $deleteQuery = NewsCategoryModel::find($newsCategoryId);
                    $deleteQuery->delete();
                    $response->code = '00';
                    $response->desc = 'Delete News Category Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'News Category Not Found.';
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
                'page' => 'numeric'
            ]);
            $search = isset($requestData['search']) && $requestData['search'] ? trim($requestData['search']) : NULL;
            $page = !empty($requestData['page']) ? trim($requestData['page']) : 0;
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            if (!$validator->fails()) {
                $offset = 0;
                $limit = 20;
                if ($page > 1) {
                    $offset = ($page - 1) * $limit;
                }
                $getList = NewsCategoryModel::getListNewsCategory(array(
                    "search" => $search,
                    "offset" => $offset,
                    "limit" => $limit
                ));
                $response->code = '00';
                $response->desc = 'Get List News Category Success!';
                $response->data = $getList;
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
                'type' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $type = isset($requestData['type']) ? trim($requestData['type']) : NULL;
            if (!$validator->fails()) {
                $getInfo = GeneralModel::where("type", $type)
                    ->where("status", 1)
                    ->first();
                if ($getInfo) {
                    $response->code = '00';
                    $response->desc = 'Get General Success!';
                    $response->data = $getInfo;
                } else {
                    $response->code = '02';
                    $response->desc = 'General Not Found.';
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
