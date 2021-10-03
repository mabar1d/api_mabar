<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use stdClass;
use Validator;


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
                User::create(
                    [
                        'username' => $requestData['username'],
                        'email' => $requestData['email'],
                        'password' => Hash::make($requestData['password']),
                    ]
                );
                $response->code = '00';
                $response->desc = 'Register Success!';
            } else {
                $response->code = '01';
                $response->desc = $validator->errors();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
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
            ]);

            if (!$validator->fails()) {
                if ($token = auth()->attempt($validator->validated())) {
                    $responseData = array(
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL() * 60,
                        'user' => auth()->user()
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
                $response->desc = $validator->errors();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
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
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
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
            $responseData = array(
                'access_token' => auth()->refresh(),
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'user' => auth()->user()
            );
            $response->code = '00';
            $response->desc = 'Refresh Token Success!';
            $response->data = $responseData;
        } catch (Exception $e) {
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
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
            $response->responseCode = '99';
            $response->responseDesc = 'Caught exception: ' .  $e->getMessage();
        }
        return response()->json($response);
    }
}
