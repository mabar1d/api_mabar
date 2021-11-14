<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\MasterReqJoinTeam;
use App\Models\MasterTeam;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;


class PersonnelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getPersonnel(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            // dd($requestData);
            $validator = Validator::make($requestData, [
                'user_id' => 'required|numeric'
            ]);
            $userId = trim($requestData['user_id']);
            if (!$validator->fails()) {
                $query = Personnel::select('personnel.*', 'm_gender.gender')
                    ->leftJoin('m_gender', 'personnel.gender_id', '=', 'm_gender.gender_id')
                    ->where('user_id', $userId);
                $execQuery = $query->get();
                if ($execQuery->first()) {
                    $response->code = '00';
                    $response->desc = 'Get Personnel Success!';
                    $response->data = $execQuery->first();
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
                $offset = $page;
                $query = Personnel::select('personnel.*', 'm_gender.gender')
                    ->leftJoin('m_gender', 'personnel.gender_id', '=', 'm_gender.gender_id');
                if ($search) {
                    $query->where('firstname', 'like', $search . '%')
                        ->orWhere('lastname', 'like', $search . '%');
                }
                $execQuery = $query->offset($offset)
                    ->limit($limit)
                    ->get();
                if ($execQuery->first()) {
                    $result = array();
                    foreach ($execQuery->toArray() as $execQuery_row) {
                        $execQuery_row['birthdate'] = !empty($execQuery_row['birthdate']) ? date('d-m-Y', strtotime(trim($execQuery_row['birthdate']))) : NULL;
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
                'gender_id' => 'required|numeric',
                'sub_district_id' => 'required|numeric',
                'district_id' => 'required|numeric',
                'province_id' => 'required|numeric',
                'zipcode' => 'required|numeric|digits:5',
                'birthdate' => 'string|date|date_format:"d-m-Y"',
                'phone' => 'numeric|digits_between:10,13'
            ]);
            $userId = $requestData['user_id'];
            if (!$validator->fails()) {
                $personnel = Personnel::where('user_id', $userId)->first();
                if ($personnel) {
                    $updateData = array(
                        'firstname' => isset($requestData['firstname']) ? trim($requestData['firstname']) : NULL,
                        'lastname' => isset($requestData['lastname']) ? trim($requestData['lastname']) : NULL,
                        'gender_id' => isset($requestData['gender_id']) ? trim($requestData['gender_id']) : NULL,
                        'address' => isset($requestData['address']) ? trim($requestData['address']) : NULL,
                        'sub_district_id' => isset($requestData['sub_district_id']) ? trim($requestData['sub_district_id']) : NULL,
                        'province_id' => isset($requestData['province_id']) ? trim($requestData['province_id']) : NULL,
                        'zipcode' => isset($requestData['zipcode']) ? trim($requestData['zipcode']) : NULL,
                        'phone' => isset($requestData['phone']) ? trim($requestData['phone']) : NULL,
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
                if ($findPersonnel && empty($findPersonnel['team_id'])) {
                    $updateData = array(
                        'role' => '2',
                    );
                    Personnel::where('user_id', $userId)
                        ->update($updateData);
                    $response->code = '00';
                    $response->desc = 'Personnel Change To Host Success!';
                } else if ($findPersonnel && !empty($findPersonnel['team_id'])) {
                    $response->code = '02';
                    $response->desc = 'Personnel already join team. Please leave team first!';
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
            $userId = trim($requestData['user_id']);
            $teamId = trim($requestData['team_id']);
            if (!$validator->fails()) {
                $findPersonnel = Personnel::where('user_id', $userId)->first();
                if ($findPersonnel && empty($findPersonnel['team_id'])) {
                    $createData = array(
                        'user_id' => $userId,
                        'team_id' => $teamId,
                        'answer' => NULL
                    );
                    MasterReqJoinTeam::updateOrCreate(['user_id' => $userId, 'team_id' => $teamId], $createData);
                    $response->code = '00';
                    $response->desc = 'Request Has Been Sent!';
                } else if ($findPersonnel && !empty($findPersonnel['team_id'])) {
                    $response->code = '02';
                    $response->desc = 'Personnel already join team. Please leave team first!';
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
}
