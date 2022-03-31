<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use App\Models\MasterTeam;
use Illuminate\Http\Request;
use App\Models\MasterTournament;
use App\Models\RatingTournament;
use App\Models\Personnel;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;

class RatingTournamentController extends Controller
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
                'tournament_id' => 'required|string',
                'team_id' => 'required|string',
                'team_lead_id' => 'required|string',
                'rating' => 'required|numeric|min:1|max:5',
                'notes' => 'string'
            ]);
            if (!$validator->fails()) {
                $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
                $userId = isset($requestData['team_lead_id']) ? trim($requestData['team_lead_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
                $rating = isset($requestData['rating']) ? trim($requestData['rating']) : NULL;
                $notes = isset($requestData['notes']) ? trim($requestData['notes']) : NULL;

                $checkTeam = MasterTeam::where('id', $teamId)
                    ->first();
                if ($checkTeam) {
                    if ($checkTeam->admin_id == $userId) {
                        $checkTournament = MasterTournament::where('id', $tournamentId)->first();
                        if ($checkTournament) {
                            if (strtotime(date('Y-m-d')) > strtotime($checkTournament->end_date)) {
                                $checkRatingTournament = RatingTournament::where('id_tournament', $tournamentId)
                                    ->where('id_team', $teamId)
                                    ->where('id_rater', $userId)
                                    ->first();
                                if (!$checkRatingTournament) {
                                    RatingTournament::updateOrCreate([
                                        'id_tournament' => $tournamentId,
                                        'id_team' => $teamId,
                                        'id_rater' => $userId,
                                        'rating' => $rating,
                                        'notes' => $notes
                                    ]);
                                    $response->code = '00';
                                    $response->desc = 'Give Rating Tournament Success!';
                                } else {
                                    $response->code = '02';
                                    $response->desc = 'You have given a rating.';
                                }
                            } else {
                                $response->code = '02';
                                $response->desc = 'Tournament is not over yet.';
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = 'Tournament Not Found.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'You Are Not Leader of This Team.';
                    }
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

    public function update(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'rating_tournament_id' => 'required|string',
                'tournament_id' => 'required|string',
                'team_id' => 'required|string',
                'team_lead_id' => 'required|string',
                'rating' => 'required|numeric|min:1|max:5',
                'notes' => 'string'
            ]);
            if (!$validator->fails()) {
                $ratingTournamentId = isset($requestData['rating_tournament_id']) ? trim($requestData['rating_tournament_id']) : NULL;
                $tournamentId = isset($requestData['tournament_id']) ? trim($requestData['tournament_id']) : NULL;
                $userId = isset($requestData['team_lead_id']) ? trim($requestData['team_lead_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;
                $rating = isset($requestData['rating']) ? trim($requestData['rating']) : NULL;
                $notes = isset($requestData['notes']) ? trim($requestData['notes']) : NULL;

                $checkTeam = MasterTeam::where('id', $teamId)
                    ->first();
                if ($checkTeam) {
                    if ($checkTeam->admin_id == $userId) {
                        $checkTournament = MasterTournament::where('id', $tournamentId)->first();
                        if ($checkTournament) {
                            if (strtotime(date('Y-m-d')) > strtotime($checkTournament->end_date)) {
                                $checkRatingTournament = RatingTournament::where('id', $ratingTournamentId)
                                    ->first();
                                if ($checkRatingTournament) {
                                    RatingTournament::where('id', $ratingTournamentId)
                                        ->update([
                                            'rating' => $rating,
                                            'notes' => $notes
                                        ]);
                                    $response->code = '00';
                                    $response->desc = 'Update Rating Tournament Success!';
                                } else {
                                    $response->code = '02';
                                    $response->desc = 'Rating Tournament Not Found.';
                                }
                            } else {
                                $response->code = '02';
                                $response->desc = 'Tournament is not over yet.';
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = 'Tournament Not Found.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'You Are Not Leader of This Team.';
                    }
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
                'rating_tournament_id' => 'required|string',
                'team_id' => 'required|string',
                'team_lead_id' => 'required|string'
            ]);
            if (!$validator->fails()) {
                $ratingTournamentId = isset($requestData['rating_tournament_id']) ? trim($requestData['rating_tournament_id']) : NULL;
                $userId = isset($requestData['team_lead_id']) ? trim($requestData['team_lead_id']) : NULL;
                $teamId = isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL;

                $checkTeam = MasterTeam::where('id', $teamId)
                    ->first();
                if ($checkTeam) {
                    if ($checkTeam->admin_id == $userId) {
                        $checkRatingTournament = RatingTournament::where('id', $ratingTournamentId)
                            ->first();
                        if ($checkRatingTournament) {
                            RatingTournament::where('id', $ratingTournamentId)
                                ->delete();
                            $response->code = '00';
                            $response->desc = 'Delete Rating Tournament Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = 'Rating Tournament Not Found.';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'You Are Not Leader of This Team.';
                    }
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
}
