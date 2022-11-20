<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use Illuminate\Http\Request;
use App\Models\MasterGame;
use App\Models\VideoModel;
use App\Models\TagModel;
use App\Models\VideoWithTagModel;
use DateTime;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class VideoController extends Controller
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
                'title' => 'required|string',
                'status' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $videoTitle = isset($requestData['title']) ? trim($requestData['title']) : NULL;
            $videoContent = isset($requestData['content']) ? trim($requestData['content']) : NULL;
            $videoLink = isset($requestData['link']) ? trim($requestData['link']) : NULL;
            $videoStatus = isset($requestData['status']) ? trim($requestData['status']) : 0;
            $videoTag = isset($requestData['tag']) ? trim($requestData['tag']) : NULL;
            // dd($requestData);
            if (!$validator->fails()) {
                $countVideo = VideoModel::countVideo(array('title' => $videoTitle));
                if ($countVideo == 0) {
                    $insertData = array(
                        'title' => $videoTitle,
                        'slug' => Str::slug($videoTitle),
                        'content' => $videoContent,
                        'link' => $videoLink,
                        'status' => $videoStatus,
                        'created_by' => $userId
                    );
                    $created = VideoModel::create($insertData);
                    $createdVideoId = $created->video_id;
                    if ($videoTagArray = json_decode($videoTag)) {
                        if (is_array($videoTagArray)) {
                            $insertVideoWithTag = array();
                            foreach ($videoTagArray as $rowNewsTag) {
                                $getNewsTag = TagModel::getRow(array("name" => strtolower($rowNewsTag)));
                                if (!$getNewsTag) {
                                    $createdNewsTag = TagModel::create([
                                        "name" => strtolower($rowNewsTag)
                                    ]);
                                }
                                $videoTagId = isset($getNewsTag["id"]) ? $getNewsTag["id"] : $createdNewsTag->id;
                                $insertVideoWithTag[] = array(
                                    "video_id" => $createdVideoId,
                                    "tag_id" => $videoTagId
                                );
                            }
                            VideoWithTagModel::insert($insertVideoWithTag);
                        }
                    }
                    $response->code = '00';
                    $response->desc = 'Create Video Success!';
                    DB::commit();
                } else {
                    $response->code = '02';
                    $response->desc = 'Video Title Already Exist.';
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
                'video_id' => 'required|string',
                'title' => 'required|string',
                'status' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $videoId = isset($requestData['video_id']) ? trim($requestData['video_id']) : NULL;
            $videoTitle = isset($requestData['title']) ? trim($requestData['title']) : NULL;
            $videoContent = isset($requestData['content']) ? trim($requestData['content']) : NULL;
            $videoLink = isset($requestData['link']) ? trim($requestData['link']) : NULL;
            $videoStatus = isset($requestData['status']) ? trim($requestData['status']) : 0;
            $videoTag = isset($requestData['tag']) ? trim($requestData['tag']) : NULL;
            if (!$validator->fails()) {
                $countVideo = VideoModel::countVideo(array('id' => $videoId));
                if ($countVideo > 0) {
                    $updateData = array(
                        'title' => $videoTitle,
                        'slug' => Str::slug($videoTitle),
                        'content' => $videoContent,
                        'link' => $videoLink,
                        'status' => $videoStatus,
                        'updated_by' => $userId
                    );
                    VideoModel::find($videoId)->update($updateData);
                    if ($videoTagArray = json_decode($videoTag)) {
                        if (is_array($videoTagArray)) {
                            $arrayVideoTagId = array();
                            foreach ($videoTagArray as $rowNewsTag) {
                                $getNewsTag = TagModel::getRow(array("name" => strtolower($rowNewsTag)));
                                if (!$getNewsTag) {
                                    $createdNewsTag = TagModel::create([
                                        "name" => strtolower($rowNewsTag)
                                    ]);
                                }
                                $videoTagId = isset($getNewsTag["id"]) ? (int)$getNewsTag["id"] : (int)$createdNewsTag->id;
                                $arrayVideoTagId[] = $videoTagId;
                                $insertVideoWithTag = array(
                                    "video_id" => $videoId,
                                    "tag_id" => $videoTagId
                                );
                                VideoWithTagModel::updateOrcreate($insertVideoWithTag);
                            }
                            //delete all Video tag where not in update tag in table news_with_tag
                            VideoWithTagModel::where("video_id", $videoId)->whereNotIn("tag_id", $arrayVideoTagId)->delete();
                        }
                    } else {
                        //delete all Video tag
                        VideoWithTagModel::where("video_id", $videoId)->delete();
                    }
                    $response->code = '00';
                    $response->desc = 'Update Video Success!';
                    DB::commit();
                } else {
                    $response->code = '02';
                    $response->desc = 'Video Not Found.';
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
                'video_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $videoId = isset($requestData['video_id']) ? trim($requestData['video_id']) : NULL;
            if (!$validator->fails()) {
                $checkExist = VideoModel::countVideo(array('id' => $videoId));
                if ($checkExist) {
                    $deleteQuery = VideoModel::find($videoId);
                    $deleteQuery->delete();
                    $response->code = '00';
                    $response->desc = 'Delete Video Success!';
                    DB::commit();
                } else {
                    $response->code = '02';
                    $response->desc = 'Video Not Found.';
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
                $getList = VideoModel::getListVideoDetail(array(
                    'status' => "1",
                    'search' => $search,
                    'offset' => $offset,
                    'limit' => $limit
                ));
                if ($getList) {
                    $resultData = array();
                    foreach ($getList as $row) {
                        if (isset($row['image']) && $row['image']) {
                            $row['image'] = URL::to("/upload/video/" . $row['image']);
                        }
                        if (isset($row['link']) && $row['link']) {
                            $row['link_youtube'] = env("WEB_YOUTUBE") . "/" . $row['link'];
                        }

                        $row['diffCreatedAt'] = $this->getDiffCreatedAt($row['created_at']);
                        $row['linkShare'] = env("WEB_DOMAIN") . "/video/" . $row["slug"];

                        //start get Video tag
                        $row['tag'] = array();
                        $getNewsTag = VideoWithTagModel::getListJoinVideoTag(array("videoId" => (int) $row["video_id"]));
                        foreach ($getNewsTag as $videoTag) {
                            $row['tag'][] = $videoTag["name"];
                        }
                        //end get Video tag

                        $resultData[] = $row;
                    }
                    $response->code = '00';
                    $response->desc = 'Get List Video Success!';
                    $response->data = $resultData;
                    DB::commit();
                } else {
                    $response->code = '02';
                    $response->desc = 'List Video is Empty.';
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
                'video_id' => 'string',
                'slug' => 'string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $videoId = isset($requestData['video_id']) ? trim($requestData['video_id']) : NULL;
            $slug = isset($requestData['slug']) ? trim($requestData['slug']) : NULL;
            if (!$validator->fails()) {
                $getInfo = array();
                if ($videoId || $slug) {
                    $getInfo = VideoModel::getVideoDetail(array('id' => $videoId, 'slug' => $slug));
                }
                if ($getInfo) {
                    if (isset($getInfo['image']) && $getInfo['image']) {
                        $getInfo['image'] = URL::to("/upload/video/" . $getInfo['image']);
                    }
                    if (isset($getInfo['link']) && $getInfo['link']) {
                        $getInfo['link_youtube'] = env("WEB_YOUTUBE") . "/" . $getInfo['link'];
                    }
                    $getInfo['diffCreatedAt'] = $this->getDiffCreatedAt($getInfo['created_at']);
                    $getInfo['linkShare'] = env("WEB_DOMAIN") . "/video/" . $getInfo["slug"];
                    //start get Video tag
                    $getInfo['tag'] = array();
                    $getNewsTag = VideoWithTagModel::getListJoinVideoTag(array("videoId" => (int) $getInfo["video_id"]));
                    foreach ($getNewsTag as $videoTag) {
                        $getInfo['tag'][] = $videoTag["name"];
                    }
                    //end get Video tag
                    $response->code = '00';
                    $response->desc = 'Get Info Video Success!';
                    $response->data = $getInfo;
                    DB::commit();
                } else {
                    $response->code = '02';
                    $response->desc = 'Video Not Found.';
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
