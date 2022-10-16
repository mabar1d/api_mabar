<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Personnel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';

        $requestData = $request->all();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'username' => 'required|string|between:2,100|unique:users',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|confirmed|min:6',
            ]);

            if (!$validator->fails()) {
                $user = User::create(
                    [
                        'username' => $requestData['username'],
                        'email' => $requestData['email'],
                        'password' => Hash::make($requestData['password']),
                    ]
                );
                if ($user) {
                    Personnel::create(
                        [
                            'user_id' => $user->id,
                        ]
                    );
                }
                $response->code = '00';
                $response->desc = 'Register Success!';
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

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';

        $requestData = $request->all();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'username' => 'required|string',
                'password' => 'required|string|min:6',
                'token_firebase' => 'string',
            ]);
            $requestData = $request->input();
            $username = isset($requestData["username"]) && $requestData["username"] ? trim($requestData["username"]) : NULL;
            $password = isset($requestData["password"]) && $requestData["password"] ? trim($requestData["password"]) : NULL;
            $tokenFirebase = isset($requestData["token_firebase"]) && $requestData["token_firebase"] ? trim($requestData["token_firebase"]) : NULL;
            if (!$validator->fails()) {
                if ($token = auth()->attempt(array("username" => $username, "password" => $password))) {
                    $responseData = array(
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL() . ' minute',
                        'user' => auth()->user()
                    );
                    $updateToken = array();
                    $updateToken["token_jwt"] = $token;
                    if ($tokenFirebase) {
                        $updateToken["token_firebase"] = $token;
                    }
                    User::where("id", auth()->user()->id)
                        ->update(
                            $updateToken
                        );
                    $response->code = '00';
                    $response->desc = 'Login Success!';
                    $response->data = $responseData;
                } else {
                    $response->code = '401';
                    $response->desc = 'Unauthorized Token!';
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

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        try {
            auth()->logout();
            $response->code = '00';
            $response->desc = 'Logout Success!';
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        return response()->json($response);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        try {
            $token = JWTAuth::getToken();
            $newToken = JWTAuth::refresh($token);
            $responseData = array(
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() . ' minute'
            );
            $response->code = '00';
            $response->desc = 'Refresh Token Success!';
            $response->data = $responseData;
        } catch (TokenExpiredException $e) {
            $response->code = '05';
            $response->desc = 'Refresh Token expired, please relogin.';
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        return response()->json($response);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        try {
            $responseData = auth()->user();
            $response->code = '00';
            $response->desc = 'Get User Profile Success!';
            $response->data = $responseData;
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        return response()->json($response);
    }

    public function checkTokenExpired()
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        try {
            $checkToken = JWTAuth::parseToken()->check();
            if ($checkToken) {
                $response->code = '00';
                $response->desc = 'Token Still Exist!';
            } else {
                $response->code = '401';
                $response->desc = 'Token Expired, Please Refresh Token!';
            }
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        return response()->json($response);
    }
}
