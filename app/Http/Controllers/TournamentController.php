<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use App\Models\MasterGame;
use App\Models\MasterTeam;
use Illuminate\Http\Request;
use App\Models\MasterTournament;
use App\Models\Personnel;
use App\Models\RatingTournament;
use App\Models\StandingTournamentMatchModel;
use App\Models\StandingTournamentModel;
use App\Models\TeamTournament;
use App\Models\TreeTournamentMatchModel;
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
                'terms_condition' => 'required|string'
            ], [
                'detail.required' => 'The description field is required.',
                'terms_condition' => 'The terms and condition is required.'
            ]);
            $userId = isset($requestData['host_id']) ? trim($requestData['host_id']) : NULL;
            $tournamentName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            if (!$validator->fails()) {
                $checkPersonnelRole = Personnel::where('user_id', $userId)
                    ->where('role', '3')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTournamentName = MasterTournament::where('name', $tournamentName)->first();
                    if (!$checkTournamentName) {
                        $insertData = array(
                            'name' => $tournamentName,
                            'id_created_by' => $userId,
                            'start_date' => date('Y-m-d', strtotime(trim($requestData['start_date']))),
                            'end_date' => date('Y-m-d', strtotime(trim($requestData['end_date']))),
                            'register_date_start' => date('Y-m-d', strtotime(trim($requestData['register_date_start']))),
                            'register_date_end' => date('Y-m-d', strtotime(trim($requestData['register_date_end']))),
                            'register_fee' => $requestData['register_fee'],
                            'detail' => $requestData['detail'],
                            'number_of_participants' => $requestData['number_of_participants'],
                            'prize' => $requestData['prize'],
                            'game_id' => $requestData['game_id'],
                            'type' => $requestData['type'],
                            'terms_condition' => $requestData['terms_condition']
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
                'type' => 'required|string',
                'terms_condition' => 'required|string'
            ], [
                'detail.required' => 'The description field is required.',
                'terms_condition' => 'The terms and condition is required.'
            ]);
            $userId = isset($requestData['host_id']) ? trim($requestData['host_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            $tournamentName = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            if (!$validator->fails()) {
                $checkPersonnelRole = Personnel::where('user_id', $userId)
                    ->where('role', '3')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTournamentId = MasterTournament::where('id', $tournamentId)->first();
                    if ($checkTournamentId) {
                        if ($checkTournamentId->id_created_by == $userId) {
                            $updateData = array(
                                'name' => $tournamentName,
                                'id_created_by' => $userId,
                                'start_date' => date('Y-m-d', strtotime(trim($requestData['start_date']))),
                                'end_date' => date('Y-m-d', strtotime(trim($requestData['end_date']))),
                                'register_date_start' => date('Y-m-d', strtotime(trim($requestData['register_date_start']))),
                                'register_date_end' => date('Y-m-d', strtotime(trim($requestData['register_date_end']))),
                                'register_fee' => $requestData['register_fee'],
                                'detail' => $requestData['detail'],
                                'number_of_participants' => $requestData['number_of_participants'],
                                'prize' => $requestData['prize'],
                                'game_id' => $requestData['game_id'],
                                'type' => $requestData['type'],
                                'terms_condition' => $requestData['terms_condition']
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
                'host_id' => 'required|string',
                'tournament_id' => 'required|string',
            ]);
            $userId = isset($requestData['host_id']) ? trim($requestData['host_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            if (!$validator->fails()) {
                $checkPersonnelRole = Personnel::where('user_id', $userId)
                    ->where('role', '3')
                    ->first();
                if ($checkPersonnelRole) {
                    $checkTournamentExist = MasterTournament::where('id', $tournamentId)->first();
                    if ($checkTournamentExist) {
                        if ($checkTournamentExist->id_created_by == $userId) {
                            MasterTournament::where('id', $tournamentId)->delete();
                            $destinationPath = 'public/upload/tournament/' . $checkTournamentExist->id . '/' . $checkTournamentExist->image;
                            if (file_exists(base_path($destinationPath))) {
                                unlink(base_path($destinationPath));
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
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
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
                        $getTeam = MasterTeam::where('id', $getPersonnel->team_id)->first();
                        if ($getTeam) {
                            if ($getTeam->admin_id == $userId) {
                                $getInfoTournament = MasterTournament::where('id', $tournamentId)->first();
                                if ($getInfoTournament) {
                                    if ($getInfoTournament->game_id == $getTeam->game_id) {
                                        if (strtotime("now") >= strtotime($getInfoTournament->register_date_start)) {
                                            if (strtotime("now") <= strtotime($getInfoTournament->register_date_end)) {
                                                $checkQuotaTournament = TeamTournament::where('tournament_id', $getInfoTournament->id)
                                                    ->where('active', '1')
                                                    ->count();
                                                if ($checkQuotaTournament < $getInfoTournament->number_of_participants) {
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
                                            $response->desc = 'Register Tournament Not Open Yet.';
                                        }
                                    } else {
                                        $response->code = '02';
                                        $response->desc = "Your Team's Game Type is not suitable for this Tournament.";
                                    }
                                } else {
                                    $response->code = '02';
                                    $response->desc = 'Tournament Not Found.';
                                }
                            } else {
                                $response->code = '02';
                                $response->desc = 'Access Forbidden. Register Tournament Must Team Leader.';
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = 'Team Not Found.';
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
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
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
                    $execQuery = $query->orderBy("updated_at", "desc");
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
                            $image = null;
                            if ($execQuery_row->image) {
                                $image = URL::to("/upload/tournament/" . $execQuery_row->id . "/" . $execQuery_row->image);
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
                            $teamInThisTournament = array();
                            $getTeamInThisTournamentArray = TeamTournament::where("tournament_id", $execQuery_row->id)->get();
                            if ($getTeamInThisTournamentArray) {
                                $getTeamInThisTournamentArray = $getTeamInThisTournamentArray->toArray();
                                foreach ($getTeamInThisTournamentArray as $rowTeamThisTournament) {
                                    $getTeamInfo = MasterTeam::find($rowTeamThisTournament["team_id"]);
                                    if ($getTeamInfo) {
                                        $getTeamInfo = $getTeamInfo->toArray();
                                        $teamInThisTournament[] = array(
                                            "team_id" => $getTeamInfo["id"],
                                            "team_name" => $getTeamInfo["name"]
                                        );
                                    }
                                }
                            }

                            $data = new stdClass;
                            $data->id = isset($execQuery_row->id) && $execQuery_row->id ? trim($execQuery_row->id) : NULL;
                            $data->name = isset($execQuery_row->name) && $execQuery_row->name ? trim($execQuery_row->name) : NULL;
                            $data->id_created_by = isset($execQuery_row->id_created_by) && $execQuery_row->id_created_by ? trim($execQuery_row->id_created_by) : NULL;
                            $data->created_name = isset($created_name) && $created_name ? trim($created_name) : NULL;
                            $data->start_date = isset($execQuery_row->start_date) && $execQuery_row->start_date ? date_format(date_create(trim($execQuery_row->start_date)), "d-m-Y") : NULL;
                            $data->end_date = isset($execQuery_row->end_date) && $execQuery_row->end_date ? date_format(date_create(trim($execQuery_row->end_date)), "d-m-Y") : NULL;
                            $data->register_date_start = isset($execQuery_row->register_date_start) && $execQuery_row->register_date_start ? date_format(date_create(trim($execQuery_row->register_date_start)), "d-m-Y") : NULL;
                            $data->register_date_end = isset($execQuery_row->register_date_end) && $execQuery_row->register_date_end ? date_format(date_create(trim($execQuery_row->register_date_end)), "d-m-Y") : NULL;
                            $data->register_fee = isset($execQuery_row->register_fee) && $execQuery_row->register_fee ? trim(number_format($execQuery_row->register_fee, 0, ",", ".")) : "0";
                            $data->type = isset($execQuery_row->type) && $execQuery_row->type ? trim($execQuery_row->type) : NULL;
                            $data->number_of_participants = isset($execQuery_row->number_of_participants) && $execQuery_row->number_of_participants ? trim(strval($execQuery_row->number_of_participants)) : NULL;
                            $data->detail = isset($execQuery_row->detail) && $execQuery_row->detail ? trim($execQuery_row->detail) : NULL;
                            $data->prize = isset($execQuery_row->prize) && $execQuery_row->prize ? trim(number_format($execQuery_row->prize, 0, ",", ".")) : "0";
                            $data->image = isset($image) && $image ? trim($image) : NULL;
                            $data->game_id = isset($execQuery_row->game_id) && $execQuery_row->game_id ? trim($execQuery_row->game_id) : NULL;
                            $data->title_game = isset($title_game) && $title_game ? trim($title_game) : NULL;
                            $data->rating = isset($ratingTournament) && $ratingTournament ? trim($ratingTournament) : NULL;
                            $data->team_in_tournament = $teamInThisTournament;
                            $data->terms_condition = isset($execQuery_row->terms_condition) && $execQuery_row->terms_condition ? trim($execQuery_row->terms_condition) : NULL;
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
        // LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
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
                        $image = URL::to("/upload/tournament/" . $getInfoTournament->id . "/" . $getInfoTournament->image);
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

                    $teamInThisTournament = array();
                    $getTeamInThisTournamentArray = TeamTournament::where("tournament_id", $getInfoTournament->id)->get();
                    if ($getTeamInThisTournamentArray) {
                        $getTeamInThisTournamentArray = $getTeamInThisTournamentArray->toArray();
                        foreach ($getTeamInThisTournamentArray as $rowTeamThisTournament) {
                            $getTeamInfo = MasterTeam::find($rowTeamThisTournament["team_id"]);
                            if ($getTeamInfo) {
                                $getTeamInfo = $getTeamInfo->toArray();
                                $teamInThisTournament[] = array(
                                    "team_id" => $getTeamInfo["id"],
                                    "team_name" => $getTeamInfo["name"]
                                );
                            }
                        }
                    }

                    $data = new stdClass;
                    $data->id = isset($getInfoTournament->id) && $getInfoTournament->id ? trim($getInfoTournament->id) : NULL;
                    $data->name = isset($getInfoTournament->name) && $getInfoTournament->name ? trim($getInfoTournament->name) : NULL;
                    $data->id_created_by = isset($getInfoTournament->id_created_by) && $getInfoTournament->id_created_by ? trim($getInfoTournament->id_created_by) : NULL;
                    $data->created_name = isset($created_name) && $created_name ? trim($created_name) : NULL;
                    $data->start_date = isset($getInfoTournament->start_date) && $getInfoTournament->start_date ? date_format(date_create(trim($getInfoTournament->start_date)), "d-m-Y") : NULL;
                    $data->end_date = isset($getInfoTournament->end_date) && $getInfoTournament->end_date ? date_format(date_create(trim($getInfoTournament->end_date)), "d-m-Y") : NULL;
                    $data->register_date_start = isset($getInfoTournament->register_date_start) && $getInfoTournament->register_date_start ? date_format(date_create(trim($getInfoTournament->register_date_start)), "d-m-Y") : NULL;
                    $data->register_date_end = isset($getInfoTournament->register_date_end) && $getInfoTournament->register_date_end ? date_format(date_create(trim($getInfoTournament->register_date_end)), "d-m-Y") : NULL;
                    $data->register_fee = isset($getInfoTournament->register_fee) && $getInfoTournament->register_fee ? trim(number_format($getInfoTournament->register_fee, 0, ",", ".")) : "0";
                    $data->type = isset($getInfoTournament->type) && $getInfoTournament->type ? trim($getInfoTournament->type) : NULL;
                    $data->number_of_participants = isset($getInfoTournament->number_of_participants) && $getInfoTournament->number_of_participants ? trim(strval($getInfoTournament->number_of_participants)) : NULL;
                    $data->detail = isset($getInfoTournament->detail) && $getInfoTournament->detail ? trim($getInfoTournament->detail) : NULL;
                    $data->prize = isset($getInfoTournament->prize) && $getInfoTournament->prize ? trim(number_format($getInfoTournament->prize, 0, ",", ".")) : "0";
                    $data->image = isset($image) && $image ? trim($image) : NULL;
                    $data->game_id = isset($getInfoTournament->game_id) && $getInfoTournament->game_id ? trim($getInfoTournament->game_id) : NULL;
                    $data->title_game = isset($title_game) && $title_game ? trim($title_game) : NULL;
                    $data->rating = isset($ratingTournament) && $ratingTournament ? trim($ratingTournament) : NULL;
                    $data->team_in_tournament = $teamInThisTournament;
                    $data->terms_condition = isset($getInfoTournament->terms_condition) && $getInfoTournament->terms_condition ? trim($getInfoTournament->terms_condition) : NULL;

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
            $requestData = $request->toArray();
            $validator = Validator::make($requestData, [
                'image_file'  => 'mimes:jpeg,jpg,png,gif|required|max:1024',
                'tournament_id' => 'required|numeric',
                'user_id' => 'required|numeric'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            if (!$validator->fails()) {
                $checkTournament = MasterTournament::where('id', $requestData['tournament_id'])
                    ->where('id_created_by', $userId)
                    ->first()->toArray();
                if ($checkTournament) {
                    if ($request->hasFile('image_file')) {
                        $filenameQuestion = 'image_tournament_' . $checkTournament['id'] . '.jpg';
                        $destinationPath = 'public/upload/tournament/' . $checkTournament['id'];
                        if (!file_exists(base_path($destinationPath))) {
                            mkdir(base_path($destinationPath), 0775, true);
                        }
                        $request->file('image_file')->move(base_path($destinationPath . '/'), $filenameQuestion);
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
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
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
                    ->orderBy('prize', 'DESC')
                    ->limit(5)
                    ->get();
                $resultData = array();
                foreach ($query as $queryRow) {
                    $data = array(
                        "id" => isset($queryRow->id) && $queryRow->id ? trim($queryRow->id) : "",
                        "title_tournament" => isset($queryRow->name) && $queryRow->name ? trim($queryRow->name) : "",
                        "image" => NULL
                    );
                    if ($queryRow->image) {
                        $data['image'] = URL::to("/image/masterTournament/" . $queryRow['id'] . "/" . $queryRow['image']);
                        $data['image'] = URL::to("/upload/tournament/" . $queryRow['id'] . "/" . $queryRow['image']);
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
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getListMyTournament(Request $request)
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
                'filter_game' => 'required|string', 'min:2',
                'type' => 'string',
            ]);
            $search = trim($requestData['search']);
            $page = !empty($requestData['page']) ? trim($requestData['page']) : 1;
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $filter_game = json_decode($requestData['filter_game'], true);
            $type = isset($requestData['type']) ? trim($requestData['type']) : NULL;
            // if (!in_array($type, ["not_open", "running", "history"])) {
            //     throw new Exception("parameter type only use not_open, running and history!", '01');
            // }

            if (!$validator->fails()) {
                if ($filter_game || empty($filter_game)) {
                    $limit = 20;
                    $query = MasterTournament::select('*')
                        ->where('id_created_by', $userId);
                    if ($type == "not_open") {
                        $query->whereRaw('DATE(register_date_start) >= DATE(NOW())');
                    } elseif ($type == "open") {
                        $query->whereRaw('DATE(register_date_start) <= DATE(NOW())');
                        $query->whereRaw('DATE(end_date) >= DATE(NOW())');
                    } elseif ($type == "history") {
                        $query->whereRaw('DATE(end_date) <= DATE(NOW())');
                    }
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
                            $image = null;
                            if ($execQuery_row->image) {
                                $image = URL::to("/upload/tournament/" . $execQuery_row->id . "/" . $execQuery_row->image);
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
                            $teamInThisTournament = array();
                            $getTeamInThisTournamentArray = TeamTournament::where("tournament_id", $execQuery_row->id)->get();
                            if ($getTeamInThisTournamentArray) {
                                $getTeamInThisTournamentArray = $getTeamInThisTournamentArray->toArray();
                                foreach ($getTeamInThisTournamentArray as $rowTeamThisTournament) {
                                    $getTeamInfo = MasterTeam::find($rowTeamThisTournament["team_id"]);
                                    if ($getTeamInfo) {
                                        $getTeamInfo = $getTeamInfo->toArray();
                                        $teamInThisTournament[] = array(
                                            "team_id" => $getTeamInfo["id"],
                                            "team_name" => $getTeamInfo["name"]
                                        );
                                    }
                                }
                            }

                            $data = new stdClass;
                            $data->id = isset($execQuery_row->id) && $execQuery_row->id ? trim($execQuery_row->id) : NULL;
                            $data->name = isset($execQuery_row->name) && $execQuery_row->name ? trim($execQuery_row->name) : NULL;
                            $data->id_created_by = isset($execQuery_row->id_created_by) && $execQuery_row->id_created_by ? trim($execQuery_row->id_created_by) : NULL;
                            $data->created_name = isset($created_name) && $created_name ? trim($created_name) : NULL;
                            $data->start_date = isset($execQuery_row->start_date) && $execQuery_row->start_date ? date_format(date_create(trim($execQuery_row->start_date)), "d-m-Y") : NULL;
                            $data->end_date = isset($execQuery_row->end_date) && $execQuery_row->end_date ? date_format(date_create(trim($execQuery_row->end_date)), "d-m-Y") : NULL;
                            $data->register_date_start = isset($execQuery_row->register_date_start) && $execQuery_row->register_date_start ? date_format(date_create(trim($execQuery_row->register_date_start)), "d-m-Y") : NULL;
                            $data->register_date_end = isset($execQuery_row->register_date_end) && $execQuery_row->register_date_end ? date_format(date_create(trim($execQuery_row->register_date_end)), "d-m-Y") : NULL;
                            $data->register_fee = isset($execQuery_row->register_fee) && $execQuery_row->register_fee ? trim(number_format($execQuery_row->register_fee, 0, ",", ".")) : "0";
                            $data->type = isset($execQuery_row->type) && $execQuery_row->type ? trim($execQuery_row->type) : NULL;
                            $data->number_of_participants = isset($execQuery_row->number_of_participants) && $execQuery_row->number_of_participants ? trim(strval($execQuery_row->number_of_participants)) : NULL;
                            $data->detail = isset($execQuery_row->detail) && $execQuery_row->detail ? trim($execQuery_row->detail) : NULL;
                            $data->prize = isset($execQuery_row->prize) && $execQuery_row->prize ? trim(number_format($execQuery_row->prize, 0, ",", ".")) : "0";
                            $data->image = isset($image) && $image ? trim($image) : NULL;
                            $data->game_id = isset($execQuery_row->game_id) && $execQuery_row->game_id ? trim($execQuery_row->game_id) : NULL;
                            $data->title_game = isset($title_game) && $title_game ? trim($title_game) : NULL;
                            $data->rating = isset($ratingTournament) && $ratingTournament ? trim($ratingTournament) : NULL;
                            $data->team_in_tournament = $teamInThisTournament;
                            $data->terms_condition = isset($execQuery_row->terms_condition) && $execQuery_row->terms_condition ? trim($execQuery_row->terms_condition) : NULL;;
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
            $response->code = $e->getCode();
            $response->desc = $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    //TOURNAMENT TREE
    public function getTournamentTreeWeb(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'string',
                'tournament_id' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            if (!$validator->fails()) {
                $checkDataExist = MasterTournament::find($tournamentId);
                // dd($checkDataExist);
                if ($checkDataExist) {
                    $urlDomain = env('WEB_DOMAIN');
                    $urlGetTournamentTree = $urlDomain . "/look_tournament/?tournament_id=" . $tournamentId;
                    $resultData = array(
                        "url" => $urlGetTournamentTree
                    );
                    $response->code = '00';
                    $response->desc = 'Get Tournament Tree Web Success!';
                    $response->data = $resultData;
                } else {
                    $response->code = '02';
                    $response->desc = "Tournament Not Found!";
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

    public function setMatchTournamentTree(Request $request)
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
                'match_array' => 'string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            $arrayMatchTournament = isset($requestData['match_array']) && $requestData['match_array'] ? json_decode($requestData['match_array'], true) : NULL;
            if (!$validator->fails()) {
                $checkDataExist = MasterTournament::find($tournamentId)->first();
                if ($checkDataExist) {
                    $tournamentDetail = $checkDataExist->toArray();
                    if ($tournamentDetail["id_created_by"] == $userId) {
                        if (strtotime(date("Y/m/d")) < strtotime($tournamentDetail["start_date"])) {
                            $round = 0;
                            foreach ($arrayMatchTournament as $rowArrayMatchTournament) {
                                $round++;
                                TreeTournamentMatchModel::updateOrCreate(
                                    [
                                        'id' => isset($rowArrayMatchTournament["match_id"]) && $rowArrayMatchTournament["match_id"] ? $rowArrayMatchTournament["match_id"] : NULL
                                    ],
                                    [
                                        'tournament_id' => $tournamentId,
                                        'tournament_phase' => 0,
                                        'round' => $round,
                                        'home_team_id' => isset($rowArrayMatchTournament["home_team_id"]) && $rowArrayMatchTournament["home_team_id"] ? $rowArrayMatchTournament["home_team_id"] : NULL,
                                        'opponent_team_id' => isset($rowArrayMatchTournament["opponent_team_id"]) && $rowArrayMatchTournament["opponent_team_id"] ? $rowArrayMatchTournament["opponent_team_id"] : NULL,
                                        'playing_date' => isset($rowArrayMatchTournament["date"]) && $rowArrayMatchTournament["date"] ? date("Y-m-d", strtotime($rowArrayMatchTournament["date"])) : NULL
                                    ]
                                );
                            }
                            $response->code = '00';
                            $response->desc = 'Set Tournament Match Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = "Tournament Is Running! Can't Save Matching Tournament";
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = "You're Not Host of This Tournament!";
                    }
                } else {
                    $response->code = '02';
                    $response->desc = "Tournament Not Found!";
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

    public function randomMatchTournamentTree(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'tournament_id' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            if (!$validator->fails()) {
                $checkDataExist = MasterTournament::where("id", $tournamentId)
                    ->first();
                if ($checkDataExist) {
                    $tournamentDetail = $checkDataExist->toArray();
                    if ($tournamentDetail["id_created_by"] == $userId) {
                        if (strtotime(date("Y/m/d")) <= strtotime($tournamentDetail["start_date"])) {
                            $getTeamTournament = TeamTournament::where("tournament_id", $tournamentId)
                                ->where("active", 1)
                                ->get();
                            if ($getTeamTournament->first()) {
                                $getTeamTournament = $getTeamTournament->toArray();
                                shuffle($getTeamTournament);
                                $maxPerMatch = 2;
                                $arrayMatchResult = $arrayPerMatch = array();
                                foreach ($getTeamTournament as $rowTeamTournament) {
                                    array_push($arrayPerMatch, $rowTeamTournament["team_id"]);
                                    if (count($arrayPerMatch) == $maxPerMatch) {
                                        // array_push($arrayMatchResult, $arrayPerMatch);
                                        // $arrayPerMatch = array();
                                    }
                                }
                                array_push($arrayMatchResult, $arrayPerMatch);

                                $resultArray = array();
                                foreach ($arrayMatchResult as $rowMatchResult) {
                                    $matching = array(
                                        "matching_id" => null,
                                        "home_team_id" => isset($rowMatchResult[0]) && $rowMatchResult[0] ? $rowMatchResult[0] : NULL,
                                        "opponent_team_id" => isset($rowMatchResult[1]) && $rowMatchResult[1] ? $rowMatchResult[1] : NULL
                                    );
                                    array_push($resultArray, $matching);
                                }
                                $response->code = '00';
                                $response->desc = 'Random Tournament Match Success!';
                                $response->data = $resultArray;
                            } else {
                                $response->code = '02';
                                $response->desc = "There's No Team Participants in This Tournament!";
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = "Tournament Is Running!";
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = "You're Not Host of This Tournament!";
                    }
                } else {
                    $response->code = '02';
                    $response->desc = "Tournament Not Found!";
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function updateScoreTournamentTree(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'match_id' => 'required|string',
                'home_score' => 'required|numeric',
                'opponent_score' => 'required|numeric'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $match_id = isset($requestData['match_id']) ? trim($requestData['match_id']) : NULL;
            $homeScore = isset($requestData['home_score']) ? trim($requestData['home_score']) : 0;
            $opponentScore = isset($requestData['opponentScore']) ? trim($requestData['opponentScore']) : 0;
            if (!$validator->fails()) {
                $checkDataExist = TreeTournamentMatchModel::getInfo(array("id" => $match_id));
                if ($checkDataExist) {
                    $tournamentDetail = MasterTournament::getInfo(array("id" => $checkDataExist["tournament_id"]));
                    if ($tournamentDetail["id_created_by"] == $userId) {
                        if (strtotime(date("Y/m/d")) >= strtotime($tournamentDetail["start_date"])) {
                            TreeTournamentMatchModel::updateOrCreate(
                                [
                                    'id' => isset($match_id) && $match_id ? $match_id : NULL
                                ],
                                [
                                    'score_home' => $homeScore,
                                    'score_opponent' => $opponentScore,
                                    'has_played' => 1
                                ]
                            );
                            $response->code = '00';
                            $response->desc = 'Update Score Match Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = "Tournament Is Not Running! Can't Update Score Match";
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = "You're Not Host of This Tournament!";
                    }
                } else {
                    $response->code = '02';
                    $response->desc = "Match ID Not Found!";
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

    public function getListMatchTournamentTree(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'string',
                'tournament_id' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            $phase = isset($requestData['phase']) ? trim($requestData['phase']) : 1;
            if (!$validator->fails()) {
                $getListMatch = TreeTournamentMatchModel::getList(array(
                    "tournamentId" => $tournamentId,
                    "tournamentPhase" => $phase
                ));
                if ($getListMatch) {
                    $data["results"] = array();
                    foreach ($getListMatch as $rowResponseData) {
                        if ($rowResponseData["tournament_phase"] == 0) {
                            $getInfoTeamHome = MasterTeam::find($rowResponseData["home_team_id"]);
                            $getInfoTeamOpponent = MasterTeam::find($rowResponseData["opponent_team_id"]);
                            $data["teams"][] = [
                                isset($getInfoTeamHome) && $getInfoTeamHome ? $getInfoTeamHome->name : "",
                                isset($getInfoTeamOpponent) && $getInfoTeamOpponent ? $getInfoTeamOpponent->name : ""
                            ];
                        }
                        $score[$rowResponseData["tournament_phase"]][] = [
                            (int)$rowResponseData["score_home"],
                            (int)$rowResponseData["score_opponent"]
                        ];
                    }
                    array_push($data["results"], $score);
                    $response->code = '00';
                    $response->desc = 'Success Get List Tree Tournament Match.';
                    $response->data = $data;
                } else {
                    $response->code = '02';
                    $response->desc = 'List Tree Tournament Match Not Found.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    //TOURNAMENT STANDING (NOT YET)
    public function randomGroupTournamentStanding(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'tournament_id' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            if (!$validator->fails()) {
                $checkDataExist = MasterTournament::where("id", $tournamentId)
                    ->first();
                if ($checkDataExist) {
                    $tournamentDetail = $checkDataExist->toArray();
                    if ($tournamentDetail["id_created_by"] == $userId) {
                        if (strtotime(date("Y/m/d")) <= strtotime($tournamentDetail["start_date"])) {
                            if ($tournamentDetail["type"] == "2") {
                                $getTeamTournament = TeamTournament::where("tournament_id", $tournamentId)
                                    ->where("active", 1)
                                    ->get();
                                if ($getTeamTournament->first()) {
                                    $getTeamTournament = $getTeamTournament->toArray();
                                    shuffle($getTeamTournament);
                                    $maxPerGroup = 4;
                                    $arrayGroupResult = $arrayPerGroup = array();
                                    $groupName = "A";
                                    foreach ($getTeamTournament as $rowTeamTournament) {
                                        $getTeamInfo = MasterTeam::getInfo(array("id" => $rowTeamTournament["team_id"]));
                                        $arrayPerGroup["group"] = $groupName;
                                        if (!isset($arrayPerGroup["team"])) {
                                            $arrayPerGroup["team"] = array();
                                        }
                                        array_push($arrayPerGroup["team"], array(
                                            "team_id" => isset($getTeamInfo["id"]) && $getTeamInfo["id"] ? $getTeamInfo["id"] : NULL,
                                            "team_name" => isset($getTeamInfo["name"]) && $getTeamInfo["name"] ? $getTeamInfo["name"] : NULL,
                                        ));
                                        if (count($arrayPerGroup["team"]) == $maxPerGroup) {
                                            array_push($arrayGroupResult, $arrayPerGroup);
                                            $arrayPerGroup["team"] = array();
                                            $groupName++;
                                        }
                                    }
                                    array_push($arrayGroupResult, $arrayPerGroup);
                                    $response->code = '00';
                                    $response->desc = 'Random Tournament Match Success!';
                                    $response->data = $arrayGroupResult;
                                } else {
                                    $response->code = '02';
                                    $response->desc = "There's No Team Participants in This Tournament!";
                                }
                            } else {
                                $response->code = '02';
                                $response->desc = "Tournament Type Is Not Standing.";
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = "Tournament Is Running!";
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = "You're Not Host of This Tournament!";
                    }
                } else {
                    $response->code = '02';
                    $response->desc = "Tournament Not Found!";
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function setGroupTournamentStanding(Request $request)
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
                'group_array' => 'string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            $GroupTournamentArray = isset($requestData['group_array']) && $requestData['group_array'] ? json_decode($requestData['group_array'], true) : NULL;
            if (!$validator->fails()) {
                $checkDataExist = MasterTournament::find($tournamentId)->first();
                if ($checkDataExist) {
                    $tournamentDetail = $checkDataExist->toArray();
                    if ($tournamentDetail["id_created_by"] == $userId) {
                        if (strtotime(date("Y/m/d")) < strtotime($tournamentDetail["start_date"])) {
                            if ($tournamentDetail["type"] == "2") {
                                foreach ($GroupTournamentArray as $rowGroupTournamentArray) {
                                    $group = isset($rowGroupTournamentArray["group"]) && $rowGroupTournamentArray["group"] ? $rowGroupTournamentArray["group"] : NULL;
                                    foreach ($rowGroupTournamentArray["team"] as $rowTeam) {
                                        StandingTournamentModel::updateOrCreate(
                                            [
                                                'id' => NULL
                                            ],
                                            [
                                                'team_id' => isset($rowTeam) && $rowTeam ? $rowTeam : NULL,
                                                'tournament_id' => $tournamentId,
                                                'group' => $group
                                            ]
                                        );
                                    }
                                }
                                DB::commit();
                                $response->code = '00';
                                $response->desc = 'Set Tournament Group Success!';
                            } else {
                                $response->code = '02';
                                $response->desc = "Tournament Type Is Not Standing.";
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = "Tournament Is Running! Can't Save Group Tournament";
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = "You're Not Host of This Tournament!";
                    }
                } else {
                    $response->code = '02';
                    $response->desc = "Tournament Not Found!";
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

    public function getInfoTournamentStanding(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'tournament_id' => 'required|string',
                'group' => 'string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            $group = isset($requestData['group']) ? trim($requestData['group']) : NULL;
            if (!$validator->fails()) {
                $checkTournamentExist = MasterTournament::getInfo(array("id" => $tournamentId));
                if ($checkTournamentExist) {
                    if ($checkTournamentExist["type"] == "2") {
                        $getListTournament = StandingTournamentModel::getList(array(
                            "tournamentId" => $tournamentId,
                            "group" => $group
                        ));
                        if ($getListTournament) {
                            $result = array();
                            foreach ($getListTournament as $rowTeamTournament) {
                                $getInfoTeam = MasterTeam::getInfo(array("id" => $rowTeamTournament["team_id"]));
                                $rowTeamTournament["team_name"] = isset($getInfoTeam["name"]) && $getInfoTeam["name"] ? $getInfoTeam["name"] : NULL;
                                $result[] = $rowTeamTournament;
                            }
                            $response->code = '00';
                            $response->desc = 'Success Get List Standing Tournament.';
                            $response->data = $result;
                        } else {
                            $response->code = '02';
                            $response->desc = 'List Standing Tournament Not Found.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Tournament Type Is Not Standing.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Tournament Not Found.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getInfoTeamTournamentStanding(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'team_id' => 'required|string'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
            if (!$validator->fails()) {
                $checkTeamExist = MasterTeam::getInfo(array("id" => $teamId));
                if ($checkTeamExist) {
                    $getInfoTeamTournament = StandingTournamentModel::getInfo(array(
                        "teamId" => $teamId
                    ));
                    if ($getInfoTeamTournament) {
                        $getInfoTeamTournament["team_name"] = isset($checkTeamExist["name"]) && $checkTeamExist["name"] ? $checkTeamExist["name"] : NULL;
                        $response->code = '00';
                        $response->desc = 'Success Get Info Team Standing Tournament.';
                        $response->data = $getInfoTeamTournament;
                    } else {
                        $response->code = '02';
                        $response->desc = 'Team Not Found In Standing Tournament.';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Team Not Found.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function setMatchTournamentStanding(Request $request)
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
                'match_array' => 'string',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
            $arrayMatchTournament = isset($requestData['match_array']) && $requestData['match_array'] ? json_decode($requestData['match_array'], true) : NULL;
            if (!$validator->fails()) {
                $checkDataExist = MasterTournament::find($tournamentId)->first();
                if ($checkDataExist) {
                    $tournamentDetail = $checkDataExist->toArray();
                    if ($tournamentDetail["id_created_by"] == $userId) {
                        if (strtotime(date("Y/m/d")) < strtotime($tournamentDetail["start_date"])) {
                            if ($tournamentDetail["type"] == "2") {
                                foreach ($arrayMatchTournament as $rowArrayMatchTournament) {
                                    StandingTournamentMatchModel::updateOrCreate(
                                        [
                                            'id' => NULL
                                        ],
                                        [
                                            'tournament_id' => $tournamentId,
                                            'home_team_id' => isset($rowArrayMatchTournament["home_team_id"]) && $rowArrayMatchTournament["home_team_id"] ? $rowArrayMatchTournament["home_team_id"] : NULL,
                                            'opponent_team_id' => isset($rowArrayMatchTournament["opponent_team_id"]) && $rowArrayMatchTournament["opponent_team_id"] ? $rowArrayMatchTournament["opponent_team_id"] : NULL,
                                            'playing_date' => isset($rowArrayMatchTournament["date"]) && $rowArrayMatchTournament["date"] ? date("Y-m-d", strtotime($rowArrayMatchTournament["date"])) : NULL
                                        ]
                                    );
                                }
                                $response->code = '00';
                                $response->desc = 'Set Tournament Match Success!';
                            } else {
                                $response->code = '02';
                                $response->desc = "Tournament Type Is Not Standing.";
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = "Tournament Is Running! Can't Save Matching Tournament";
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = "You're Not Host of This Tournament!";
                    }
                } else {
                    $response->code = '02';
                    $response->desc = "Tournament Not Found!";
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

    public function updateScoreTournamentStanding(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'match_id' => 'required|string',
                'home_score' => 'required|numeric',
                'opponent_score' => 'required|numeric'
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $match_id = isset($requestData['match_id']) ? trim($requestData['match_id']) : NULL;
            $homeScore = isset($requestData['home_score']) ? trim($requestData['home_score']) : 0;
            $opponentScore = isset($requestData['opponentScore']) ? trim($requestData['opponentScore']) : 0;
            if (!$validator->fails()) {
                $checkDataExist = StandingTournamentMatchModel::getInfo(array("id" => $match_id));
                if ($checkDataExist) {
                    $tournamentDetail = MasterTournament::getInfo(array("id" => $checkDataExist["tournament_id"]));
                    if ($tournamentDetail["id_created_by"] == $userId) {
                        if (strtotime(date("Y/m/d")) >= strtotime($tournamentDetail["start_date"])) {
                            StandingTournamentMatchModel::updateOrCreate(
                                [
                                    'id' => isset($match_id) && $match_id ? $match_id : NULL
                                ],
                                [
                                    'score_home' => $homeScore,
                                    'score_opponent' => $opponentScore,
                                    'has_played' => 1
                                ]
                            );
                            $response->code = '00';
                            $response->desc = 'Update Score Match Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = "Tournament Is Not Running! Can't Update Score Match";
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = "You're Not Host of This Tournament!";
                    }
                } else {
                    $response->code = '02';
                    $response->desc = "Match ID Not Found!";
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
