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
        $user = Auth::user();

        // Check if the user is authenticated and is an author with ACTIVE status
        if (!$user || $user->user_type !== \App\Models\User::USER_TYPE_AUTHOR || $user->status !== 'ACTIVE') {
            return response()->json(['message' => 'Access denied. Only active authors can perform this action.'], 403);
        }

        return $next($request);
    }
}
