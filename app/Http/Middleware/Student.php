<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Role;


class Student
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $roleName = Role::where('id', auth()->user()->role->role_id)->first()->name;
        if ($roleName !== 'student') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return $next($request);
    }
}
