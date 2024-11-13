<?php

namespace App\Http\Controllers\API;

use App\Classes\APIResponseClass;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'image_profile' => null,
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => bcrypt($request->password),
            ]);

            $token = JWTAuth::fromUser($user);

            DB::commit();

            return APIResponseClass::sendResponse([
                'user'  => $user,
                'token' => $token
            ], 'User registered successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return APIResponseClass::rollback($e, 'Registration failed. Please try again.');
        }
    }

    /**
     * Handle user login using JWT.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        DB::beginTransaction();

        try {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();

                $token = JWTAuth::fromUser($user);

                DB::commit();

                return APIResponseClass::sendResponse([
                    'user'  => $user,
                    'token' => $token
                ], 'Login successful!');
            }

            return APIResponseClass::sendResponse([], 'Invalid credentials.', 401, false);
        } catch (Exception $e) {
            DB::rollBack();
            return APIResponseClass::rollback($e, 'Login failed. Please try again.');
        }
    }

    /**
     * Handle user logout.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        DB::beginTransaction();

        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            DB::commit();

            return APIResponseClass::sendResponse([], 'Logout successful!');
        } catch (Exception $e) {
            DB::rollBack();
            return APIResponseClass::rollback($e, 'Logout failed. Please try again.');
        }
    }
}