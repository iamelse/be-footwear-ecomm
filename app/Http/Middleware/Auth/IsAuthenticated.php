<?php

namespace App\Http\Middleware\Auth;

use App\Classes\APIResponseClass;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class IsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($user) {
                return $next($request);
            }

        } catch (Exception $e) {
            return APIResponseClass::sendResponse(
                [],
                'Unauthorized, Please provide a valid token.',
                401,
                false
            );
        }

        return APIResponseClass::sendResponse(
            [],
            'Unauthorized.',
            401,
            false
        );
    }
}
