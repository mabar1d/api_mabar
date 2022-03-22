<?php

namespace App\Http\Controllers;

use App\Models\MasterGame;
use Illuminate\Http\Request;
use App\Models\MasterTournament;
use App\Models\Personnel;
use App\Models\RatingTournament;
use App\Models\TeamTournament;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;

class TournamentController extends Controller
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
                'host_id' => 'required|string',
                'name' => 'required|string',
                'detail' => 'required|string',
                'number_of_participants' => 'required|numeric|max:16',
                'register_date_start' => 'required|string',
                'register_date_end' => 'required|string',
                'register_fee' => 'required|numeric',
                'start_date' => 'required|string',
                'end_date' => 'required|string',
                'prize' => 'required|numeric',
                'game_id' => 'required|string',
                'type' => 'required|string',
            ]);
            $hostId = isset($requestData['host_id']) ? trim($requestData['host_id']) : NULL;
            $tournamentName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            if (!$validator->fails()) {
                $checkPersonnelRole = Personnel::where('user_id', $hostId)
                    ->where('role', '3')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTournamentName = MasterTournament::where('name', $tournamentName)->first();
                    if (!$checkTournamentName) {
                        $insertData = array(
                            'name' => $tournamentName,
                            'id_created_by' => $hostId,
                            'start_date' => date('Y-m-d', strtotime(trim($requestData['start_date']))),
                            'end_date' => date('Y-m-d', strtotime(trim($requestData['end_date']))),
                            'register_date_start' => date('Y-m-d', strtotime(trim($requestData['register_date_start']))),
                            'register_date_end' => date('Y-m-d', strtotime(trim($requestData['register_date_end']))),
                            'register_fee' => $requestData['register_fee'],
                            'detail' => $requestData['detail'],
                            'number_of_participants' => $requestData['number_of_participants'],
                            'prize' => $requestData['prize'],
                            'game_id' => $requestData['game_id'],
                            'type' => $requestData['type']
                        );
                        $getCreatedData = MasterTournament::create($insertData);
                        $data = new stdClass();
                        $data->tournament_id = $getCreatedData->id;
                        $response->code = '00';
                        $response->desc = 'Create Tournament Success!';
                        $response->data = $data;
                    } else {
                        $response->code = '02';
                        $response->desc = 'Tournament Name Already Exist.';
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

    public function update(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'host_id' => 'required|string',
                'tournament_id' => 'required|string',
                'name' => 'required|string',
                'detail' => 'required|string',
                'number_of_participants' => 'required|numeric',
                'register_date_start' => 'required|string',
                'register_date_end' => 'required|string',
                'register_fee' => 'required|numeric',
                'start_date' => 'required|string',
                'end_date' => 'required|string',
                'prize' => 'required|numeric',
                'game_id' => 'required|string',
                'type' => 'required|string'
            ]);
            $hostId = isset($requestData['host_id']) ? trim($requestData['host_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            $tournamentName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            if (!$validator->fails()) {
                $checkPersonnelRole = Personnel::where('user_id', $hostId)
                    ->where('role', '3')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTournamentId = MasterTournament::where('id', $tournamentId)->first();
                    if ($checkTournamentId) {
                        if ($checkTournamentId->id_created_by == $hostId) {
                            $updateData = array(
                                'name' => $tournamentName,
                                'id_created_by' => $hostId,
                                'start_date' => date('Y-m-d', strtotime(trim($requestData['start_date']))),
                                'end_date' => date('Y-m-d', strtotime(trim($requestData['end_date']))),
                                'register_date_start' => date('Y-m-d', strtotime(trim($requestData['register_date_start']))),
                                'register_date_end' => date('Y-m-d', strtotime(trim($requestData['register_date_end']))),
                                'register_fee' => $requestData['register_fee'],
                                'detail' => $requestData['detail'],
                                'number_of_participants' => $requestData['number_of_participants'],
                                'prize' => $requestData['prize'],
                                'game_id' => $requestData['game_id'],
                                'type' => $requestData['type']
                            );
                            MasterTournament::where('id', $tournamentId)
                                ->update($updateData);
                            $response->code = '00';
                            $response->desc = 'Update Tournament Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = 'You not have access update this tournament.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Tournament Not Exist.';
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

    public function delete(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'host_id' => 'required|string',
                'tournament_id' => 'required|string',
            ]);
            $hostId = isset($requestData['host_id']) ? trim($requestData['host_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            if (!$validator->fails()) {
                $checkPersonnelRole = Personnel::where('user_id', $hostId)
                    ->where('role', '3')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTournamentExist = MasterTournament::where('id', $tournamentId)->first();
                    if ($checkTournamentExist) {
                        if ($checkTournamentExist->id_created_by == $hostId) {
                            MasterTournament::where('id', $tournamentId)->delete();
                            $destinationPath = 'app/public/upload/tournament/' . $checkTournamentExist->id . '/' . $checkTournamentExist->image;
                            if (file_exists(storage_path($destinationPath))) {
                                unlink(storage_path($destinationPath));
                            }
                            $response->code = '00';
                            $response->desc = 'Delete Tournament Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = 'You not have access delete this tournament.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Tournament Not Found.';
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

    public function registerTournament(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            // $requestUser = auth()->user()->toArray();
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
                'tournament_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            if (!$validator->fails()) {
                $getPersonnel = Personnel::where('user_id', $userId)->first();
                if ($getPersonnel) {
                    if (isset($getPersonnel->team_id) && $getPersonnel->team_id) {
                        $getInfoTournament = MasterTournament::where('id', $tournamentId)->first();
                        if ($getInfoTournament) {
                            if ((strtotime("now") >= strtotime($getInfoTournament->register_date_start)) && (strtotime("now") <= strtotime($getInfoTournament->register_date_end))) {
                                $checkQuotaTournament = TeamTournament::where('tournament_id', $getInfoTournament->id)
                                    ->where('active', '1')
                                    ->count();
                                if ($checkQuotaTournament <= $getInfoTournament->number_of_participants) {
                                    $insertData = array(
                                        'team_id' => $getPersonnel->team_id,
                                        'tournament_id' => $tournamentId,
                                        'active' => '1',
                                        'created_by' => $userId,
                                    );;
                                    if (TeamTournament::firstOrCreate($insertData)->wasRecentlyCreated) {
                                        $response->code = '00';
                                        $response->desc = 'Register Tournament Success!';
                                    } else {
                                        $response->code = '01';
                                        $response->desc = 'Team is Registered on this Tournament.';
                                    }
                                } else {
                                    $response->code = '02';
                                    $response->desc = 'Quota Tournament is Full.';
                                }
                            } else {
                                $response->code = '02';
                                $response->desc = 'Register Tournament Closed.';
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = 'Tournament Not Found.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Personnel Not Have Team.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found.';
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

    public function getListTournament(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            // $requestUser = auth()->user()->toArray();
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'search' => 'string',
                'page' => 'numeric',
                'filter_game' => 'required|string', 'min:2'
            ]);
            $search = trim($requestData['search']);
            $page = !empty($requestData['page']) ? trim($requestData['page']) : 1;
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            if (!$validator->fails()) {
                $filter_game = json_decode($requestData['filter_game'], true);
                if ($filter_game || empty($filter_game)) {
                    $limit = 20;
                    $query = MasterTournament::select('*');
                    if (isset($filter_game) && $filter_game) {
                        $query->whereIn('game_id', $filter_game);
                    }
                    if ($search) {
                        $query->where('name', 'like', $search . '%');
                    }
                    if ($page > 1) {
                        $offset = ($page - 1) * $limit;
                        $query->offset($offset);
                    }
                    $execQuery = $query->limit($limit)
                        ->get();
                    if ($execQuery->first()) {
                        $result = array();
                        foreach ($execQuery as $execQuery_row) {
                            $getPersonnel = Personnel::where('user_id', $execQuery_row->id_created_by)->first();
                            $getRatingTournament = RatingTournament::selectRaw('count(*) as total_rater, sum(rating) as total_rating')
                                ->where('id_tournament', $execQuery_row->id)
                                ->groupBy('id_tournament')
                                ->first();

                            $ratingTournament = 0;
                            if ($getRatingTournament) {
                                $ratingTournament = round($getRatingTournament->total_rating / $getRatingTournament->total_rater);
                            }
                            if ($execQuery_row->image) {
                                $image = URL::to("/image/masterTournament/" . $execQuery_row->id . "/" . $execQuery_row->image);
                            }
                            $title_game = NULL;
                            if (isset($execQuery_row->game_id) && $execQuery_row->game_id) {
                                $getMasterGame = MasterGame::where('id', $execQuery_row->game_id)->first();
                                $title_game = $getMasterGame->title;
                            }
                            $getPersonnel = Personnel::where('user_id', $execQuery_row->id_created_by)->first();
                            if ($getPersonnel) {
                                $created_name = $getPersonnel->firstname . ' ' . $getPersonnel->lastname;
                            }

                            $data = new stdClass;
                            $data->id = isset($execQuery_row->id) && $execQuery_row->id ? trim($execQuery_row->id) : NULL;
                            $data->name = isset($execQuery_row->name) && $execQuery_row->name ? trim($execQuery_row->name) : NULL;
                            $data->id_created_by = isset($execQuery_row->id_created_by) && $execQuery_row->id_created_by ? trim($execQuery_row->id_created_by) : NULL;
                            $data->created_name = isset($created_name) && $created_name ? trim($created_name) : NULL;
                            $data->start_date = isset($execQuery_row->start_date) && $execQuery_row->start_date ? trim($execQuery_row->start_date) : NULL;
                            $data->end_date = isset($execQuery_row->end_date) && $execQuery_row->end_date ? trim($execQuery_row->end_date) : NULL;
                            $data->register_date_start = isset($execQuery_row->register_date_start) && $execQuery_row->register_date_start ? trim($execQuery_row->register_date_start) : NULL;
                            $data->register_date_end = isset($execQuery_row->register_date_end) && $execQuery_row->register_date_end ? trim($execQuery_row->register_date_end) : NULL;
                            $data->register_fee = isset($execQuery_row->register_fee) && $execQuery_row->register_fee ? $execQuery_row->register_fee : 0;
                            $data->type = isset($execQuery_row->type) && $execQuery_row->type ? trim($execQuery_row->type) : NULL;
                            $data->number_of_participants = isset($execQuery_row->number_of_participants) && $execQuery_row->number_of_participants ? trim($execQuery_row->number_of_participants) : NULL;
                            $data->detail = isset($execQuery_row->detail) && $execQuery_row->detail ? trim($execQuery_row->detail) : NULL;
                            $data->prize = isset($execQuery_row->prize) && $execQuery_row->prize ? $execQuery_row->prize : 0;
                            $data->image = isset($image) && $image ? trim($image) : NULL;
                            $data->game_id = isset($execQuery_row->game_id) && $execQuery_row->game_id ? trim($execQuery_row->game_id) : NULL;
                            $data->title_game = isset($title_game) && $title_game ? trim($title_game) : NULL;
                            $data->rating = isset($ratingTournament) && $ratingTournament ? trim($ratingTournament) : NULL;
                            array_push($result, $data);
                        }
                        $response->code = '00';
                        $response->desc = 'Get List Tournament Success!';
                        $response->data = $result;
                    } else {
                        $response->code = '02';
                        $response->desc = 'List Tournament is Empty.';
                    }
                } else {
                    $response->code = '01';
                    $response->desc = 'Parameter filter_game must array.';
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

    public function getInfoTournament(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            // $requestUser = auth()->user()->toArray();
            $validator = Validator::make($requestData, [
                'tournament_id' => 'required|string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            if (!$validator->fails()) {
                $getInfoTournament = MasterTournament::where('id', $tournamentId)->first();
                if ($getInfoTournament) {
                    $getRatingTournament = RatingTournament::selectRaw('count(*) as total_rater, sum(rating) as total_rating')
                        ->where('id_tournament', $getInfoTournament->id)
                        ->groupBy('id_tournament')
                        ->first();
                    $ratingTournament = 0;
                    if ($getRatingTournament) {
                        $ratingTournament = round($getRatingTournament->total_rating / $getRatingTournament->total_rater);
                    }
                    if ($getInfoTournament->image) {
                        $image = URL::to("/image/masterTournament/" . $getInfoTournament->id . "/" . $getInfoTournament->image);
                    }
                    $title_game = NULL;
                    if (isset($getInfoTournament->game_id) && $getInfoTournament->game_id) {
                        $getMasterGame = MasterGame::where('id', $getInfoTournament->game_id)->first();
                        $title_game = $getMasterGame->title;
                    }
                    $getPersonnel = Personnel::where('user_id', $getInfoTournament->id_created_by)->first();
                    if ($getPersonnel) {
                        $created_name = $getPersonnel->firstname . ' ' . $getPersonnel->lastname;
                    }

                    $data = new stdClass;
                    $data->id = isset($getInfoTournament->id) && $getInfoTournament->id ? trim($getInfoTournament->id) : NULL;
                    $data->name = isset($getInfoTournament->name) && $getInfoTournament->name ? trim($getInfoTournament->name) : NULL;
                    $data->id_created_by = isset($getInfoTournament->id_created_by) && $getInfoTournament->id_created_by ? trim($getInfoTournament->id_created_by) : NULL;
                    $data->created_name = isset($created_name) && $created_name ? trim($created_name) : NULL;
                    $data->start_date = isset($getInfoTournament->start_date) && $getInfoTournament->start_date ? trim($getInfoTournament->start_date) : NULL;
                    $data->end_date = isset($getInfoTournament->end_date) && $getInfoTournament->end_date ? trim($getInfoTournament->end_date) : NULL;
                    $data->register_date_start = isset($getInfoTournament->register_date_start) && $getInfoTournament->register_date_start ? trim($getInfoTournament->register_date_start) : NULL;
                    $data->register_date_end = isset($getInfoTournament->register_date_end) && $getInfoTournament->register_date_end ? trim($getInfoTournament->register_date_end) : NULL;
                    $data->register_fee = isset($getInfoTournament->register_fee) && $getInfoTournament->register_fee ? $getInfoTournament->register_fee : 0;
                    $data->type = isset($getInfoTournament->type) && $getInfoTournament->type ? trim($getInfoTournament->type) : NULL;
                    $data->number_of_participants = isset($getInfoTournament->number_of_participants) && $getInfoTournament->number_of_participants ? trim($getInfoTournament->number_of_participants) : NULL;
                    $data->detail = isset($getInfoTournament->detail) && $getInfoTournament->detail ? trim($getInfoTournament->detail) : NULL;
                    $data->prize = isset($getInfoTournament->prize) && $getInfoTournament->prize ? $getInfoTournament->prize : 0;
                    $data->image = isset($image) && $image ? trim($image) : NULL;
                    $data->game_id = isset($getInfoTournament->game_id) && $getInfoTournament->game_id ? trim($getInfoTournament->game_id) : NULL;
                    $data->title_game = isset($title_game) && $title_game ? trim($title_game) : NULL;
                    $data->rating = isset($ratingTournament) && $ratingTournament ? trim($ratingTournament) : NULL;

                    $response->code = '00';
                    $response->desc = 'Get Info Tournament Success!';
                    $response->data = $data;
                } else {
                    $response->code = '02';
                    $response->desc = 'Tournament Not Found.';
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
            $requestData = $request->toArray();
            $validator = Validator::make($requestData, [
                'image_file'  => 'mimes:jpeg,jpg,png,gif|required|max:1024',
                'tournament_id' => 'required|numeric',
                'user_id' => 'required|numeric'
            ]);
            if (!$validator->fails()) {
                $checkTournament = MasterTournament::where('id', $requestData['tournament_id'])
                    ->where('id_created_by', $requestData['user_id'])
                    ->first()->toArray();
                if ($checkTournament) {
                    if ($request->hasFile('image_file')) {
                        $file = $request->file('image_file');
                        $fileExtension = $file->getClientOriginalExtension();
                        $filenameQuestion = 'image_tournament_' . $checkTournament['id'] . '.jpg';
                        $destinationPath = 'app/public/upload/tournament/' . $checkTournament['id'];
                        if (!file_exists(storage_path($destinationPath))) {
                            mkdir(storage_path($destinationPath), 0775, true);
                        }
                        $request->file('image_file')->move(storage_path($destinationPath . '/'), $filenameQuestion);
                        MasterTournament::where('id', $checkTournament['id'])
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
                    $response->code = '02';
                    $response->desc = 'Tournament Not Found.';
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

    public function getCarouselTournament(Request $request)
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
                $query = MasterTournament::select('*')
                    ->where('register_date_start', '>=', date("Y-m-d"))
                    ->limit(5)
                    ->get();
                $resultData = array();
                foreach ($query as $queryRow) {
                    $data = array(
                        "id" => isset($queryRow->id) && $queryRow->id ? trim($queryRow->id) : ""
                    );
                    if ($queryRow->image) {
                        $data['image'] = URL::to("/image/masterTournament/" . $queryRow['id'] . "/" . $queryRow['image']);
                    }
                    array_push($resultData, $data);
                }
                $response->code = '00';
                $response->desc = 'Get Carousel Tournament Success!';
                $response->data = $resultData;
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
