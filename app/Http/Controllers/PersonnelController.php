<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\MasterReqJoinTeam;
use App\Models\MasterTeam;
use App\Models\MasterTournament;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;

class PersonnelController extends Controller
{
    public function __construct(Request $request)
    {
        $token = $request->bearerToken();
        if ($token != env('GOD_BEARER')) {
            $this->middleware('auth:api');
        }
    }

    public function getPersonnel(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            // if (isset($request->newToken) && $request->newToken) {
            //     $response->newToken = $request->newToken;
            // }
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric'
            ]);
            $userId = trim($requestData['user_id']);
            if (!$validator->fails()) {
                $query = Personnel::select('users.username', 'personnel.*', 'm_gender.gender')
                    ->leftJoin('m_gender', 'personnel.gender_id', '=', 'm_gender.gender_id')
                    ->leftJoin('users', 'personnel.user_id', '=', 'users.id')
                    ->where('personnel.user_id', $userId);
                $execQuery = $query->first();
                if (isset($execQuery->birthdate) && $execQuery->birthdate) {
                    $execQuery->birthdate = date("d-m-Y", strtotime($execQuery->birthdate));
                }
                if (isset($execQuery->image) && $execQuery->image) {
                    // $execQuery->image = URL::to("/image/personnel/" . $execQuery->user_id . "/" . $execQuery->image);
                    $execQuery->image = URL::to("/upload/personnel/" . $execQuery->user_id . "/" . $execQuery->image);
                }
                if ($execQuery) {
                    $response->code = '00';
                    $response->desc = 'Get Personnel Success!';
                    $response->data = $execQuery;
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found';
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

    public function getListPersonnel(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            // dd($requestData);
            $validator = Validator::make($requestData, [
                'search' => 'string',
                'page' => 'numeric',
            ]);
            $search = trim($requestData['search']);
            $page = !empty($requestData['page']) ? trim($requestData['page']) : 0;
            if (!$validator->fails()) {
                $limit = 20;
                $query = Personnel::select('users.username', 'personnel.*', 'm_gender.gender')
                    ->leftJoin('m_gender', 'personnel.gender_id', '=', 'm_gender.gender_id')
                    ->leftJoin('users', 'personnel.user_id', '=', 'users.id');
                if ($search) {
                    $query->where('personnel.firstname', 'like', $search . '%')
                        ->orWhere('personnel.lastname', 'like', $search . '%')
                        ->orWhere('users.username', 'like', $search . '%');
                }
                if ($page > 1) {
                    $offset = ($page - 1) * $limit;
                    $query->offset($offset);
                }
                $execQuery = $query->limit($limit)
                    ->get();
                if ($execQuery->first()) {
                    $result = array();
                    foreach ($execQuery->toArray() as $execQuery_row) {
                        $execQuery_row['birthdate'] = !empty($execQuery_row['birthdate']) ? date('d-m-Y', strtotime(trim($execQuery_row['birthdate']))) : NULL;
                        if (isset($execQuery_row['image']) && $execQuery_row['image']) {
                            // $execQuery_row['image'] = URL::to("/image/personnel/" . $execQuery_row['user_id'] . "/" . $execQuery_row['image']);
                            $execQuery_row['image'] = URL::to("/upload/personnel/" . $execQuery_row['user_id'] . "/" . $execQuery_row['image']);
                        }
                        array_push($result, $execQuery_row);
                    }
                    $response->code = '00';
                    $response->desc = 'Get Personnel Success!';
                    $response->data = $result;
                } else {
                    $response->code = '02';
                    $response->desc = 'List Personnel Not Found.';
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

    public function updateInfoPersonnel(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'firstname' => 'required|string',
                'ign' => 'required|string',
                'gender_id' => 'required|numeric',
                // 'sub_district_id' => 'required|numeric',
                // 'district_id' => 'required|numeric',
                // 'province_id' => 'required|numeric',
                'zipcode' => 'required|numeric|digits:5',
                'birthdate' => 'string|date|date_format:"d-m-Y"',
                'phone' => 'numeric|digits_between:10,13'
            ], [
                'ign.required' => 'The in game name field is required.'
            ]);
            $userId = $requestData['user_id'];
            if (!$validator->fails()) {
                $personnel = Personnel::where('user_id', $userId)->first();
                if ($personnel) {
                    $updateData = array(
                        'firstname' => isset($requestData['firstname']) ? trim($requestData['firstname']) : NULL,
                        'lastname' => isset($requestData['lastname']) ? trim($requestData['lastname']) : NULL,
                        'ign' => isset($requestData['ign']) ? trim($requestData['ign']) : NULL,
                        'gender_id' => isset($requestData['gender_id']) ? trim($requestData['gender_id']) : NULL,
                        'address' => isset($requestData['address']) ? trim($requestData['address']) : NULL,
                        'sub_district_id' => isset($requestData['sub_district_id']) ? trim($requestData['sub_district_id']) : NULL,
                        'province_id' => isset($requestData['province_id']) ? trim($requestData['province_id']) : NULL,
                        'zipcode' => isset($requestData['zipcode']) ? trim($requestData['zipcode']) : NULL,
                        'phone' => isset($requestData['phone']) ? trim($requestData['phone']) : NULL,
                        'is_verified' => '1' //hardcode setelah update data pasti menjadi verified user
                    );
                    if (isset($requestData['birthdate']) && $requestData['birthdate']) {
                        $updateData['birthdate'] = date('Y-m-d', strtotime(trim($requestData['birthdate'])));
                    }
                    $updatePersonnel = Personnel::where('user_id', $userId)
                        ->update($updateData);
                    $response->code = '00';
                    $response->desc = 'Update Info Personnel Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found';
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

    public function updateTeamPersonnel(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
                'team_id' => 'required|numeric',
            ]);
            $userId = $requestData['user_id'];
            if (!$validator->fails()) {
                $personnel = Personnel::where('user_id', $userId)->first();
                if ($personnel) {
                    $updateData = array(
                        'team_id' => isset($requestData['team_id']) ? trim($requestData['team_id']) : NULL,
                    );
                    $updatePersonnel = Personnel::where('user_id', $userId)
                        ->update($updateData);
                    $response->code = '00';
                    $response->desc = 'Update Team Personnel Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found';
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

    public function personnelReqMember(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
            ]);
            $userId = $requestData['user_id'];
            if (!$validator->fails()) {
                $findPersonnel = Personnel::where('user_id', $userId)->first();
                if ($findPersonnel) {
                    if ($findPersonnel->is_verified == 1) {
                        if (empty($findPersonnel->team_id)) {
                            $passValidate = true;
                            if ($findPersonnel->role == 3) {
                                if (empty($findPersonnel->team_id)) {
                                    $checkTournamentExist = MasterTournament::where('id_created_by', $userId)
                                        ->whereRaw("DATE(end_date) >= DATE(NOW())")
                                        ->first();
                                    if ($checkTournamentExist) {
                                        $response->code = '02';
                                        $response->desc = 'Personnel already have a tournament going on. Please wait until the tournament is over!';
                                        $passValidate = false;
                                    }
                                } else {
                                    $response->code = '02';
                                    $response->desc = 'Personnel already join team. Please leave team first!';
                                    $passValidate = false;
                                }
                            }
                            if ($passValidate) {
                                $updateData = array(
                                    'role' => '1',
                                );
                                Personnel::where('user_id', $userId)
                                    ->update($updateData);
                                $response->code = '00';
                                $response->desc = 'Personnel Change To Member Success!';
                            }
                        } else {
                            $response->code = '02';
                            $response->desc = 'Personnel already join team. Please leave team first!';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Please Complete the Profile First!';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found';
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

    public function personnelReqTeamLead(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
            ]);
            $userId = $requestData['user_id'];
            if (!$validator->fails()) {
                $findPersonnel = Personnel::where('user_id', $userId)->first();
                if ($findPersonnel) {
                    if ($findPersonnel->is_verified == 1) {
                        if (empty($findPersonnel->team_id)) {
                            $updateData = array(
                                'role' => '2',
                            );
                            Personnel::where('user_id', $userId)
                                ->update($updateData);
                            $response->code = '00';
                            $response->desc = 'Personnel Change To Team Leader Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = 'Personnel already join team. Please leave team first!';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Please Complete the Profile First!';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found';
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

    public function personnelReqHost(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
            ]);
            $userId = $requestData['user_id'];
            if (!$validator->fails()) {
                $findPersonnel = Personnel::where('user_id', $userId)->first();
                if ($findPersonnel) {
                    if ($findPersonnel->is_verified == 1) {
                        if (empty($findPersonnel->team_id)) {
                            $updateData = array(
                                'role' => '3',
                            );
                            Personnel::where('user_id', $userId)
                                ->update($updateData);
                            $response->code = '00';
                            $response->desc = 'Personnel Change To Host Success!';
                        } else {
                            $response->code = '02';
                            $response->desc = 'Personnel already join team. Please leave team first!';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Please Complete your profile first!';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found';
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

    public function personnelReqJoinTeam(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
                'team_id' => 'required|numeric',
            ]);
            if (!$validator->fails()) {
                $userId = trim($requestData['user_id']);
                $teamId = trim($requestData['team_id']);

                $findTeam = MasterTeam::where('id', $teamId)->first();
                if ($findTeam) {
                    $findPersonnel = Personnel::where('user_id', $userId)->first();
                    if ($findPersonnel) {
                        if (empty($findPersonnel->team_id)) {
                            $createData = array(
                                'user_id' => $userId,
                                'team_id' => $teamId,
                                'answer' => NULL
                            );
                            MasterReqJoinTeam::updateOrCreate(['user_id' => $userId, 'team_id' => $teamId], $createData);
                            $response->code = '00';
                            $response->desc = 'Request Has Been Sent!';
                        } else {
                            $response->code = '02';
                            $response->desc = 'Personnel already join team. Please leave team first!';
                        }
                    } else {
                        $response->code = '02';
                        $response->desc = 'Personnel Not Found';
                    }
                } else {
                    $response->code = '02';
                    $response->desc = 'Team Not Found';
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

    public function personnelLeaveTeam(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric',
            ]);
            $userId = trim($requestData['user_id']);
            if (!$validator->fails()) {
                $findPersonnel = Personnel::where('user_id', $userId)->first();
                if ($findPersonnel) {
                    $getListPersonnelTeam = json_decode(MasterTeam::where('id', $findPersonnel['team_id'])->first()['personnel']);
                    $removeListPersonnelTeam = array_diff($getListPersonnelTeam, [$userId]);
                    MasterTeam::where('id', $findPersonnel['team_id'])
                        ->update(array('personnel' => $removeListPersonnelTeam));
                    $updateData = array(
                        'team_id' => NULL
                    );
                    Personnel::where('user_id', $userId)
                        ->update($updateData);
                    $response->code = '00';
                    $response->desc = 'Personnel Leave Team Success!';
                } else {
                    $response->code = '02';
                    $response->desc = 'Personnel Not Found';
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
            $requestData = $request->all();
            $validator = Validator::make($requestData, [
                'image_file'  => 'mimes:jpeg,jpg,png,gif|required|max:2048',
                'user_id' => 'required|numeric'
            ]);
            if (!$validator->fails()) {
                $checkExist = Personnel::where('user_id', $requestData['user_id'])
                    ->first()->toArray();
                if ($checkExist) {
                    if ($request->hasFile('image_file')) {
                        $filenameQuestion = 'image_person_' . $checkExist['user_id'] . '.jpg';
                        $destinationPath = 'public/upload/personnel/' . $checkExist['user_id'];
                        if (!file_exists(base_path($destinationPath))) {
                            mkdir(base_path($destinationPath), 0775, true);
                        }
                        $request->file('image_file')->move(base_path($destinationPath . '/'), $filenameQuestion);
                        Personnel::where('user_id', $checkExist['user_id'])
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
                    $response->desc = 'User Not Found.';
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

    public function getPersonnelNotMember(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            // dd($requestData);
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'search' => 'string',
                'page' => 'required|numeric'
            ]);
            if (!$validator->fails()) {
                $search = trim($requestData['search']);
                $page = !empty($requestData['page']) ? trim($requestData['page']) : 1;
                $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;

                $limit = 20;
                $query = Personnel::select('users.username', 'personnel.*', 'm_gender.gender')
                    ->leftJoin('m_gender', 'personnel.gender_id', '=', 'm_gender.gender_id')
                    ->leftJoin('users', 'personnel.user_id', '=', 'users.id')
                    ->whereNotIn('personnel.is_verified', array('1'))
                    ->whereNull('personnel.team_id');
                if ($search) {
                    $query->where('personnel.firstname', 'like', $search . '%')
                        ->orWhere('personnel.lastname', 'like', $search . '%')
                        ->orWhere('users.username', 'like', $search . '%');
                }
                if ($page > 1) {
                    $offset = ($page - 1) * $limit;
                    $query->offset($offset);
                }
                $execQuery = $query->limit($limit)
                    ->get();
                if ($execQuery->first()) {
                    $result = array();
                    foreach ($execQuery->toArray() as $execQuery_row) {
                        $execQuery_row['birthdate'] = !empty($execQuery_row['birthdate']) ? date('d-m-Y', strtotime(trim($execQuery_row['birthdate']))) : NULL;
                        if (isset($execQuery_row['image']) && $execQuery_row['image']) {
                            // $execQuery_row['image'] = URL::to("/image/personnel/" . $execQuery_row['user_id'] . "/" . $execQuery_row['image']);
                            $execQuery_row['image'] = URL::to("/upload/personnel/" . $execQuery_row['user_id'] . "/" . $execQuery_row['image']);
                        }
                        array_push($result, $execQuery_row);
                    }
                    $response->code = '00';
                    $response->desc = 'Get List Personnel Not Member Success!';
                    $response->data = $result;
                } else {
                    $response->code = '02';
                    $response->desc = 'List Personnel Not Member is Empty.';
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
}
