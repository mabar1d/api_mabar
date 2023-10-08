<?php

namespace App\Http\Controllers;

use App\Helpers\FcmFirebase;
use App\Models\LogApi;
use Illuminate\Http\Request;
use App\Models\NewsCategoryModel;
use App\Models\NewsModel;
use App\Models\TagModel;
use App\Models\NewsWithTagModel;
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
            $this->middleware('auth:api', ['except' => ['getList', 'getInfo']]);
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
            $newsTag = isset($requestData['tag']) ? trim($requestData['tag']) : NULL;
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
                        $createdNewsId = $created->id;
                        if ($request->hasFile('image')) {
                            $fileName = 'news_' . $createdNewsId . '.jpg';
                            $destinationPath = 'public/upload/news/';
                            if (!file_exists(base_path($destinationPath))) {
                                mkdir(base_path($destinationPath), 0775, true);
                            }
                            $request->file('image')->move(base_path($destinationPath . '/'), $fileName);
                            NewsModel::find($createdNewsId)
                                ->update([
                                    'image' => $fileName
                                ]);
                        }
                        if ($newsTagArray = json_decode($newsTag)) {
                            if (is_array($newsTagArray)) {
                                $insertNewsWithTag = array();
                                foreach ($newsTagArray as $rowNewsTag) {
                                    $getNewsTag = TagModel::getRow(array("name" => strtolower($rowNewsTag)));
                                    if (!$getNewsTag) {
                                        $createdNewsTag = TagModel::create([
                                            "name" => strtolower($rowNewsTag)
                                        ]);
                                    }
                                    $newsTagId = isset($getNewsTag["id"]) ? $getNewsTag["id"] : $createdNewsTag->id;
                                    $insertNewsWithTag[] = array(
                                        "news_id" => $createdNewsId,
                                        "news_tag_id" => $newsTagId
                                    );
                                }
                                NewsWithTagModel::insert($insertNewsWithTag);
                            }
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
            $newsId = isset($requestData['news_id']) ? (int) trim($requestData['news_id']) : NULL;
            $newsCategoryId = isset($requestData['news_category_id']) ? trim($requestData['news_category_id']) : NULL;
            $newsTitle = isset($requestData['title']) ? trim($requestData['title']) : NULL;
            $newsContent = isset($requestData['content']) ? trim($requestData['content']) : NULL;
            $newsStatus = isset($requestData['status']) ? trim($requestData['status']) : 0;
            $newsTag = isset($requestData['tag']) ? trim($requestData['tag']) : NULL;
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
                        if ($newsTagArray = json_decode($newsTag)) {
                            if (is_array($newsTagArray)) {
                                $arrayNewsTagId = array();
                                foreach ($newsTagArray as $rowNewsTag) {
                                    $getNewsTag = TagModel::getRow(array("name" => strtolower($rowNewsTag)));
                                    if (!$getNewsTag) {
                                        $createdNewsTag = TagModel::create([
                                            "name" => strtolower($rowNewsTag)
                                        ]);
                                    }
                                    $newsTagId = isset($getNewsTag["id"]) ? (int)$getNewsTag["id"] : (int)$createdNewsTag->id;
                                    $arrayNewsTagId[] = $newsTagId;
                                    $insertNewsWithTag = array(
                                        "news_id" => $newsId,
                                        "news_tag_id" => $newsTagId
                                    );
                                    NewsWithTagModel::updateOrcreate($insertNewsWithTag);
                                }

                                //delete all news tag where not in update tag in table news_with_tag
                                NewsWithTagModel::where("news_id", $newsId)->whereNotIn("news_tag_id", $arrayNewsTagId)->delete();
                            }
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
                'user_id' => 'string',
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
                $resultData = array();
                $getList = NewsModel::where("status", 1)
                    ->offset($offset)
                    ->limit($limit);
                if (isset($search) && $search) {
                    $getList = $getList->where("title", 'like', $search . '%');
                }
                $getList = $getList->get();
                if (!$getList) {
                    throw new Exception("List News Empty!", 1);
                }
                foreach ($getList as $rowNews) {
                    $newsCategoryName = isset($rowNews->newsCategory) ? $rowNews->newsCategory->name : NULL;
                    $newsTags = $rowNews->pivotNewsTags->pluck("name", "id")->toArray();

                    $getInfo = [
                        "id" => $rowNews->id,
                        "news_category_id" => $rowNews->news_category_id,
                        "news_category_name" => $newsCategoryName,
                        "title" => $rowNews->title,
                        "slug" => $rowNews->slug,
                        "link_share" => env("WEB_DOMAIN") . "/news/" . $rowNews["slug"],
                        "content" => $rowNews->content,
                        "image" => $rowNews->image,
                        "news_image_url" => url("/upload/news/") . $rowNews["image"],
                        "tag" => $newsTags,
                        "status" => $rowNews->status,
                        "created_by" => $rowNews->created_by,
                        "created_at" => $rowNews->created_at,
                        "creator_name" => $rowNews->created_by,
                        "diffCreatedAt" => $this->getDiffCreatedAt($rowNews->created_at)
                    ];
                    $resultData[] = $getInfo;
                }

                $response->code = '00';
                $response->desc = 'Get List News Success!';
                $response->data = $resultData;
                DB::commit();
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
                'user_id' => 'string',
                'news_id' => 'string',
                'slug' => 'string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $newsId = isset($requestData['news_id']) ? trim($requestData['news_id']) : NULL;
            $getInfo = array();
            if (!$validator->fails()) {
                if (!$newsId) {
                    throw new Exception("News ID Is Empty!", 1);
                }
                $data = NewsModel::find($newsId);
                if (!$data) {
                    throw new Exception("News Not Found!", 1);
                }
                $newsCategoryName = isset($data->newsCategory) ? $data->newsCategory->name : NULL;
                $newsTags = $data->pivotNewsTags->pluck("name", "id")->toArray();

                $getInfo = [
                    "id" => $data->id,
                    "news_category_id" => $data->news_category_id,
                    "news_category_name" => $newsCategoryName,
                    "title" => $data->title,
                    "slug" => $data->slug,
                    "link_share" => env("WEB_DOMAIN") . "/news/" . $data["slug"],
                    "content" => $data->content,
                    "image" => $data->image,
                    "news_image_url" => url("/upload/news/") . $data["image"],
                    "tag" => $newsTags,
                    "status" => $data->status,
                    "created_by" => $data->created_by,
                    "created_at" => $data->created_at,
                    "creator_name" => $data->created_by,
                    "diffCreatedAt" => $this->getDiffCreatedAt($data->created_at)
                ];

                $response->code = 00;
                $response->desc = 'Get Info News Success!';
                $response->data = $getInfo;
                DB::commit();
            } else {
                throw new Exception($validator->errors()->first(), 1);
            }
        } catch (Exception $e) {
            DB::rollback();
            $response->code = $e->getCode();
            $response->desc = $e->getMessage();
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
            if (!$validator->fails()) {
                if ($request->hasFile('image_file')) {
                    $fileName = bin2hex(openssl_random_pseudo_bytes(10)) . '.jpg';
                    $destinationPath = 'public/upload/news/';
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

    public function getListWeb(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'string',
                'search' => 'string',
                'offset' => 'numeric',
                'limit' => 'numeric'
            ]);
            $search = isset($requestData['search']) && $requestData['search'] ? trim($requestData['search']) : NULL;
            $offset = !empty($requestData['offset']) ? trim($requestData['offset']) : 0;
            $limit = !empty($requestData['limit']) ? trim($requestData['limit']) : 4;
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            if (!$validator->fails()) {
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

                    //start get news tag
                    $getInfo['tag'] = array();
                    $getNewsTag = NewsWithTagModel::getListJoinNewsTag(array("newsId" => (int) $row["id"]));
                    foreach ($getNewsTag as $newsTag) {
                        $row['tag'][] = $newsTag["name"];
                    }
                    //end get news tag

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
}
