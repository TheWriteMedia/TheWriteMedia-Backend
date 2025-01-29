<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user is an app admin
        if (Auth::user() && Auth::user()->user_type !== \App\Models\User::USER_TYPE_AUTHOR) {
            return response()->json(['message' => 'Access denied. Only authors can perform this action.'], 403);
        }

        return $next($request);
    }
}
