<?php

namespace App\Http\Controllers;

use App\Models\JobNotifFirebaseModel;
use App\Models\LogApi;
use Illuminate\Http\Request;
use App\Models\MasterGame;
use App\Models\MasterTeam;
use App\Models\MasterTournament;
use App\Models\Personnel;
use App\Models\VideoModel;
use App\Models\TagModel;
use App\Models\TeamTournament;
use App\Models\VideoWithTagModel;
use DateTime;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Str;

class ChatController extends Controller
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

    public function postTournamentChat(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'tournament_id' => 'required|string',
                'message' => 'required|string',
                'action_url' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            $message = isset($requestData['message']) ? trim($requestData['message']) : NULL;
            $actionUrl = isset($requestData['action_url']) ? trim($requestData['action_url']) : NULL;
            // dd($requestData);
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 1);
            }

            $checkTournament = MasterTournament::find($tournamentId);
            if (!$checkTournament) {
                throw new Exception("Tournament Not Found!", 1);
            }

            $getTeamTournament = TeamTournament::where("tournament_id", $tournamentId)
                ->where("active", 1)
                ->get()
                ->toArray();
            foreach ($getTeamTournament as $rowTeamTournament) {
                $getTeam = MasterTeam::find($rowTeamTournament["team_id"]);
                if (!$getTeam) {
                    throw new Exception("Team Not Found!", 1);
                }
                foreach (explode(",", $getTeam["personnel"]) as $rowTeamPersonnel) {
                    $getPersonnel = Personnel::find($rowTeamPersonnel);
                    // if (!$getPersonnel) {
                    //     throw new Exception("Personnel Not Found!", 1);
                    // }
                    $notifBody = [
                        'data' => [
                            'tournament_id' => $tournamentId
                        ],
                        'message' => $message
                    ];
                    JobNotifFirebaseModel::create([
                        'notif_type' => 'notif_tournament',
                        'client_key' => isset($getPersonnel["token_firebase"]) && $getPersonnel["token_firebase"] ? $getPersonnel["token_firebase"] : NULL,
                        'notif_title' => 'Tournament Notification',
                        'notif_body' => json_encode($notifBody),
                        'notif_img_url' => NULL,
                        'notif_url' => $actionUrl,
                        'status' => 0
                    ]);
                }
            }

            $response->code = '00';
            $response->desc = 'Success Send Tournament Notification!';
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->code = strval($e->getCode());
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
                    if ($request->hasFile('image')) {
                        $fileName = 'video_' . $videoId . '.jpg';
                        $destinationPath = 'public/upload/video/';
                        if (!file_exists(base_path($destinationPath))) {
                            mkdir(base_path($destinationPath), 0775, true);
                        }
                        $request->file('image')->move(base_path($destinationPath . '/'), $fileName);
                        VideoModel::find($videoId)
                            ->update([
                                'image' => $fileName
                            ]);
                    }
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
                $getList = VideoModel::where("status", 1)
                    ->offset($offset)
                    ->limit($limit);
                if (isset($search) && $search) {
                    $getList = $getList->where("title", 'like', $search . '%');
                }
                if ($getList->count() == 0) {
                    throw new Exception("Video List Is Empty!", 1);
                }
                $getList = $getList->get();

                $resultData = array();
                foreach ($getList as $row) {
                    $getInfo = [];
                    $videoCategoryName = isset($row->category) ? $row->category->name : null;
                    $videoTags = $row->pivotVideoTags->pluck("name", "id")->toArray();
                    $createdByName = isset($row->user) ? $row->user->username : null;
                    $getInfo = [
                        "video_id" => $row->video_id,
                        "category_id" => $row->category_id,
                        "category_name" => $videoCategoryName,
                        "title" => $row->title,
                        "slug" => $row->slug,
                        "content" => $row->content,
                        "link" => env("WEB_YOUTUBE") . "/" . $row->link,
                        "linkShare" => env("WEB_DOMAIN") . "/video/" . $row->slug,
                        "tag" => $videoTags,
                        "notify" => $row->notify,
                        "status" => $row->status,
                        "created_by" => $row->created_by,
                        "created_by_name" => $createdByName,
                        "created_at" => $row->created_at,
                        "diffCreatedAt" => $this->getDiffCreatedAt($row->created_at)
                    ];
                    $resultData[] = $getInfo;
                }

                $response->code = '00';
                $response->desc = 'Get List Video Success!';
                $response->data = $resultData;
                DB::commit();
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            DB::rollback();
            $response->code = strval($e->getCode());
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
            $getInfo = array();
            if (!$validator->fails()) {
                if (!$videoId) {
                    throw new Exception("Video ID Is Empty!", 1);
                }
                $data = VideoModel::find($videoId);
                if (!$data) {
                    throw new Exception("Video Not Found!", 1);
                }
                $videoCategoryName = isset($data->category) ? $data->category->name : null;
                $videoTags = $data->pivotVideoTags->pluck("name", "id")->toArray();
                $createdByName = isset($data->user) ? $data->user->username : null;
                $getInfo = [
                    "video_id" => $data->video_id,
                    "category_id" => $data->category_id,
                    "category_name" => $videoCategoryName,
                    "title" => $data->title,
                    "slug" => $data->slug,
                    "content" => $data->content,
                    "link" => env("WEB_YOUTUBE") . "/" . $data->link,
                    "linkShare" => env("WEB_DOMAIN") . "/video/" . $data->slug,
                    "tag" => $videoTags,
                    "notify" => $data->notify,
                    "status" => $data->status,
                    "created_by" => $data->created_by,
                    "created_by_name" => $createdByName,
                    "created_at" => $data->created_at,
                    "diffCreatedAt" => $this->getDiffCreatedAt($data->created_at)
                ];
                $response->code = '00';
                $response->desc = 'Get Info Video Success!';
                $response->data = $getInfo;
                DB::commit();
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            DB::rollback();
            $response->code = $e->getCode();
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
