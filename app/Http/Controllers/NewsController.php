<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use Illuminate\Http\Request;
use App\Models\MasterGame;
use App\Models\NewsCategoryModel;
use App\Models\NewsModel;
use DateTime;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function __construct(Request $request)
    {
        $token = $request->bearerToken();
        if ($token != env('GOD_BEARER')) {
            $this->middleware('auth:api');
        }
    }

    static function getDiffCreatedAt($createdAt)
    {
        $datetime1 = new DateTime(date("Y-m-d H:i:s", strtotime($createdAt)));
        $datetime2 = new DateTime(date("Y-m-d H:i:s"));
        $interval = $datetime1->diff($datetime2);
        $minutes = $interval->format('%i');
        $hours = $interval->format('%h');
        $days = $interval->format('%a');
        if ($days >= 1) {
            $diffTime = $days . " days ago";
        } else if ($hours > 0 && $hours < 24) {
            $diffTime = $hours . " hours ago";
        } else {
            $diffTime = $minutes . " minutes ago";
        }
        return $diffTime;
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
                'news_category_id' => 'required|string',
                'title' => 'required|string',
                'status' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $newsCategoryId = isset($requestData['news_category_id']) ? trim($requestData['news_category_id']) : NULL;
            $newsTitle = isset($requestData['title']) ? trim($requestData['title']) : NULL;
            $newsContent = isset($requestData['content']) ? trim($requestData['content']) : NULL;
            $newsStatus = isset($requestData['status']) ? trim($requestData['status']) : 0;
            if (!$validator->fails()) {
                $countCategoryNews = NewsCategoryModel::countNewsCategory(array('id' => $newsCategoryId));
                if ($countCategoryNews > 0) {
                    $countNews = NewsModel::countNews(array('title' => $newsTitle));
                    if ($countNews == 0) {
                        $insertData = array(
                            'news_category_id' => $newsCategoryId,
                            'title' => $newsTitle,
                            'slug' => Str::slug($newsTitle),
                            'content' => $newsContent,
                            'status' => $newsStatus,
                            'created_by' => $userId
                        );
                        $created = NewsModel::create($insertData);
                        if ($request->hasFile('image')) {
                            $fileName = 'news_' . $created->id . '.jpg';
                            $destinationPath = 'public/upload/news/';
                            if (!file_exists(base_path($destinationPath))) {
                                mkdir(base_path($destinationPath), 0775, true);
                            }
                            $request->file('image')->move(base_path($destinationPath . '/'), $fileName);
                            NewsModel::find($created->id)
                                ->update([
                                    'image' => $fileName
                                ]);
                        }
                        $response->code = '00';
                        $response->desc = 'Create News Success!';
                        DB::commit();
                    } else {
                        $response->code = '02';
                        $response->desc = 'News Title Already Exist.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'News Category Not Found.';
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
                'news_id' => 'required|string',
                'news_category_id' => 'required|string',
                'title' => 'required|string',
                'status' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $newsId = isset($requestData['news_id']) ? trim($requestData['news_id']) : NULL;
            $newsCategoryId = isset($requestData['news_category_id']) ? trim($requestData['news_category_id']) : NULL;
            $newsTitle = isset($requestData['title']) ? trim($requestData['title']) : NULL;
            $newsContent = isset($requestData['content']) ? trim($requestData['content']) : NULL;
            $newsStatus = isset($requestData['status']) ? trim($requestData['status']) : 0;
            if (!$validator->fails()) {
                $countCategoryNews = NewsCategoryModel::countNewsCategory(array('id' => $newsCategoryId));
                if ($countCategoryNews > 0) {
                    $countNews = NewsModel::countNews(array('id' => $newsId));
                    if ($countNews > 0) {
                        $updateData = array(
                            'news_category_id' => $newsCategoryId,
                            'title' => $newsTitle,
                            'slug' => Str::slug($newsTitle),
                            'content' => $newsContent,
                            'status' => $newsStatus,
                            'updated_by' => $userId
                        );
                        NewsModel::find($newsId)->update($updateData);
                        if ($request->hasFile('image')) {
                            $fileName = 'news_' . $newsId . '.jpg';
                            $destinationPath = 'public/upload/news/';
                            if (!file_exists(base_path($destinationPath))) {
                                mkdir(base_path($destinationPath), 0775, true);
                            }
                            $request->file('image')->move(base_path($destinationPath . '/'), $fileName);
                            NewsModel::find($newsId)
                                ->update([
                                    'image' => $fileName
                                ]);
                        }
                        $response->code = '00';
                        $response->desc = 'Update News Success!';
                        DB::commit();
                    } else {
                        $response->code = '02';
                        $response->desc = 'News Not Found.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'News Category Not Found.';
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
                'news_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $newsId = isset($requestData['news_id']) ? trim($requestData['news_id']) : NULL;
            if (!$validator->fails()) {
                $checkExist = NewsModel::countNews(array('id' => $newsId));
                if ($checkExist) {
                    $deleteQuery = NewsModel::find($newsId);
                    $deleteQuery->delete();
                    $response->code = '00';
                    $response->desc = 'Delete News Success!';
                    DB::commit();
                } else {
                    $response->code = '02';
                    $response->desc = 'News Not Found.';
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
                $getList = NewsModel::getListNewsDetail(array(
                    'status' => "1",
                    'search' => $search,
                    'offset' => $offset,
                    'limit' => $limit
                ));
                // if ($getList) {
                $resultData = array();
                foreach ($getList as $row) {
                    if ($row['image']) {
                        $row['image'] = URL::to("/upload/news/" . $row['image']);
                    }
                    $row['diffCreatedAt'] = $this->getDiffCreatedAt($row['created_at']);
                    $row['linkShare'] = env("WEB_DOMAIN") . "/news/" . $row["slug"];
                    $resultData[] = $row;
                }
                $response->code = '00';
                $response->desc = 'Get List News Success!';
                $response->data = $resultData;
                DB::commit();
                // } else {
                //     $response->code = '02';
                //     $response->desc = 'List News is Empty.';
                // }
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
                'news_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $newsId = isset($requestData['news_id']) ? trim($requestData['news_id']) : NULL;
            if (!$validator->fails()) {
                $getInfo = NewsModel::getNewsDetail(array('id' => $newsId));
                if ($getInfo) {
                    if ($getInfo['image']) {
                        $getInfo['image'] = URL::to("/upload/news/" . $getInfo['image']);
                    }
                    $getInfo['diffCreatedAt'] = $this->getDiffCreatedAt($getInfo['created_at']);
                    $getInfo['linkShare'] = env("WEB_DOMAIN") . "/news/" . $getInfo["slug"];
                    $response->code = '00';
                    $response->desc = 'Get Info News Category Success!';
                    $response->data = $getInfo;
                    DB::commit();
                } else {
                    $response->code = '02';
                    $response->desc = 'News Category Not Found.';
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
                    $destinationPath = 'public/upload/masterGame/' . $gameId;
                    if (!file_exists(base_path($destinationPath))) {
                        mkdir(base_path($destinationPath), 0775, true);
                    }
                    $request->file('image_file')->move(base_path($destinationPath . '/'), $filenameQuestion);
                    MasterGame::where('id', $gameId)
                        ->update([
                            'image' => $filenameQuestion
                        ]);
                    $response->code = '00';
                    $response->desc = 'Upload Success.';
                    DB::commit();
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
}
