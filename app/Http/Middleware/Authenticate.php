<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Contracts\Auth\Factory as Auth;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $expired = false;
        try {
            if ($token = JWTAuth::getToken()) {
                JWTAuth::checkOrFail();
            }
        } catch (TokenExpiredException $e) {
            $expired = true;
        } catch (JWTException $e) {
            return response(array(
                'code' => '05',
                'desc' => 'Token Not Valid, please relogin.'
            ), 200);
        }
        if ($expired) {
            try {
                JWTAuth::refresh();
            } catch (TokenExpiredException $e) {
                return response(array(
                    'code' => '05',
                    'desc' => 'Token expired, please relogin.'
                ), 200);
            } catch (JWTException $e) {
                return response(array(
                    'code' => '05',
                    'desc' => 'Token Not Valid, please relogin.'
                ), 200);
            }
            // send the refreshed token back to the client
        }
        return $next($request);

        // // Check for token requested.
        // if (!JWTAuth::getToken()) {
        //     if ($request->acceptsJson() || $request->expectsJson()) {
        //         return JsonResponse::create([
        //             'error' => \Lang::get('auth.token_not_provided')
        //         ], Response::HTTP_UNAUTHORIZED);
        //     } else
        //         throw new BadRequestException('Token not provided');
        // }
        // try {
        //     // Refresh the token requested.
        //     $newToken = JWTAuth::refresh();
        // } catch (TokenBlacklistedException $e) {
        //     return JsonResponse::create([
        //         'error' => \Lang::get('auth.token_blacklisted')
        //     ], Response::HTTP_UNAUTHORIZED);
        // } catch (TokenExpiredException $e) {
        //     return JsonResponse::create([
        //         'error' => \Lang::get('auth.token_expired')
        //     ], 419);
        // } catch (JWTException $e) {
        //     if ($request->acceptsJson() || $request->expectsJson()) {
        //         return JsonResponse::create([
        //             'error' => \Lang::get('auth.token_absent')
        //         ], Response::HTTP_UNAUTHORIZED);
        //     } else
        //         throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
        // }
        // // Defined response header.
        // $response = $next($request);
        // // Send the refreshed token back to the client.
        // return $this->setAuthenticationHeader($response, $newToken);

        // $token = JWTAuth::getToken();
        // $newToken = JWTAuth::refresh($token);
        // dd($token, $newToken);
        // try {
        //     $user = JWTAuth::parseToken()->authenticate();
        // } catch (Exception $e) {
        //     if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
        //         return response()->json(['status' => 'Token is Invalid']);
        //     } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
        //         return response()->json(['status' => 'Token is Expired']);
        //     } else {
        //         return response()->json(['status' => 'Authorization Token not found']);
        //     }
        // }
        // return $next($request);

        // $expired = false;
        // try {
        //     if ($token = JWTAuth::getToken()) {
        //         JWTAuth::checkOrFail();
        //     }
        //     // JWTAuth::authenticate();
        // } catch (TokenExpiredException $e) {
        //     $expired = true;
        // } catch (JWTException $e) {
        //     return response('token_invalid 1', 401);
        // }
        // if ($expired) {
        //     try {
        //         $newToken = JWTAuth::refresh(true, true);
        //         JWTAuth::setToken($newToken);
        //         $request->request->add(['newToken' => $newToken]);
        //     } catch (TokenExpiredException $e) {
        //         if ($this->auth->guard($guard)->guest()) {
        //             return response('Unauthorized.', 401);
        //         }
        //         // return response('refresh_expired', 401);
        //     } catch (JWTException $e) {
        //         return response('token_invalid 2', 401);
        //     }
        //     // send the refreshed token back to the client
        // }
        // return $next($request);

        // try {
        //     JWTAuth::parseToken()->authenticate();
        // } catch (TokenExpiredException $e) {
        //     dd('Page must be refreshed');
        // }
        // dd(JWTAuth::parseToken()->authenticate());
        // $now = time();
        // if ($now >= JWTAuth::parseToken()->getClaim('exp') - 10) {
        //     // dd($now - 360, JWTAuth::parseToken()->getClaim('exp'), 'need refresh');
        //     $token_data = array(
        //         'access_token' => auth()->refresh(),
        //         'token_type' => 'bearer',
        //         'expires_in' => auth()->factory()->getTTL() * 60
        //     );
        //     $request->request->add(['token_data' => $token_data]);
        //     return $next($request);
        // } else {
        //     if ($this->auth->guard($guard)->guest()) {
        //         return response('Unauthorized.', 401);
        //     }
        // }
        // return $next($request);
        // dd($now - 360, JWTAuth::parseToken()->getClaim('exp'), 'no need refresh');
        // try {
        //     JWTAuth::parseToken()->authenticate();
        // } catch (TokenExpiredException $e) {
        //     return 'must refresh token';
        //     // $responseData['token_data'] = array(
        //     //     'access_token' => auth()->refresh(),
        //     //     'token_type' => 'bearer',
        //     //     'expires_in' => auth()->factory()->getTTL() * 60
        //     // );
        //     // return response()->json($responseData);
        // }
        // if ($this->auth->guard($guard)->guest()) {
        //     return response('Unauthorized.', 401);
        // }
        // try {
        //     JWTAuth::parseToken()->authenticate();
        // } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        //     // do whatever you want to do if a token is expired
        //     // dd('token expired');
        //     $token_data = array(
        //         'access_token' => auth()->refresh(),
        //         'token_type' => 'bearer',
        //         'expires_in' => auth()->factory()->getTTL() * 60
        //     );
        //     $request->request->add(['token_data' => $token_data]);
        //     return $next($request);
        // } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        //     // do whatever you want to do if a token is invalid
        //     dd('token invalid');
        // } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        //     // do whatever you want to do if a token is not present
        //     dd('token not present');
        // }


        // $expired = false;
        // // if (!$token = $this->auth->setRequest($request)->getToken()) {
        // //     return response('token_not_provided', 400);
        // // }
        // try {
        //     JWTAuth::parseToken()->authenticate();
        // } catch (TokenExpiredException $e) {
        //     $expired = true;
        // } catch (JWTException $e) {
        //     return response('token_invalid 1', 401);
        // }
        // if ($expired) {
        //     try {
        //         $newToken = auth()->refresh();
        //         // JWTAuth::authenticate($newToken);
        //     } catch (TokenExpiredException $e) {
        //         return response('refresh_expired', 401);
        //     } catch (JWTException $e) {
        //         return response('token_invalid 2', 401);
        //     }
        //     // send the refreshed token back to the client
        //     $request->headers->set('Authorization', 'Bearer ' . $newToken);
        //     $request->request->add(['newToken' => $newToken]);
        //     // return response('token success refresh : ' . $newToken, 200);
        // }
        // // if (!$user) {
        // //     return response('user_not_found', 404);
        // // }
        // // $this->events->fire('tymon.jwt.valid', $user);
        // return $next($request);

        // $expired = false;
        // try {
        //     if ($token = JWTAuth::getToken()) {
        //         JWTAuth::checkOrFail();
        //     }
        //     // JWTAuth::authenticate();
        // } catch (TokenExpiredException $e) {
        //     $expired = true;
        // } catch (JWTException $e) {
        //     return response('token_invalid 1', 401);
        // }
        // if ($expired) {
        //     try {
        //         $newToken = JWTAuth::refresh();
        //         JWTAuth::setToken($newToken);
        //         $request->request->add(['newToken' => $newToken]);
        //     } catch (TokenExpiredException $e) {
        //         return response('refresh_expired', 401);
        //     } catch (JWTException $e) {
        //         return response('token_invalid 2', 401);
        //     }
        //     // send the refreshed token back to the client
        // }
        // return $next($request);

        // dd(JWTAuth::getToken());
        // if ($token = JWTAuth::getToken()) {
        //     try {
        //         $user = JWTAuth::authenticate($token);
        //     } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        //         dd($e);
        //         $newToken = JWTAuth::refresh();
        //         JWTAuth::setToken($newToken);
        //         return response()->json([
        //             'status' => 500,
        //             'message' => 'Token Expired',
        //             'newToken' => $newToken,
        //             'expires_in' => auth()->factory()->getTTL() * 60
        //         ], 500);
        //     } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        //         return response()->json([
        //             'status' => 500,
        //             'message' => 'Token Invalid',
        //         ], 500);
        //     } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        //         return response()->json([
        //             'status' => 500,
        //             'message' => $e->getMessage(),
        //         ], 500);
        //     }
        //     return $next($request);
        // } else {
        //     return response()->json([
        //         'status' => 500,
        //         'message' => "unauthorized",
        //     ], 500);
        // }

        // dd(JWTAuth::parseToken()->authenticate());
        // try {
        //     if (!JWTAuth::parseToken()->authenticate()) {
        //         return response()->json("", 401);
        //     }
        //     //refresh token
        //     $newtoken = JWTAuth::refresh(); // or assign original token that hasn't expired yet.
        //     return $this->authService->respondWithToken($newtoken);
        // } catch (TokenExpiredException $e) {
        //     // Access token has expired
        //     try {
        //         $newtoken = JWTAuth::refresh();
        //         return $this->authService->respondWithToken($newtoken);
        //     } catch (TokenExpiredException $e) {
        //         // Refresh token has expired
        //         return response()->json([
        //             "message" => $e->getMessage()
        //         ], 401);
        //     } catch (TokenBlacklistedException $e) {
        //         // Access token has be list to blacklist. You must re-log into the system.
        //         return response()->json([
        //             "message" => $e->getMessage()
        //         ], 401);
        //     }
        // } catch (\Exception $e) {
        //     return response()->json(['test'], 400);
        // }
    }
}
