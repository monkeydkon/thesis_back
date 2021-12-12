<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;

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
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        $user = User::where('email', request(['email']))->first();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userRole = UserRole::where('user_id', $user->id)->first();
        $role = Role::where('id', $userRole->role_id)->first();

        if (!$token = auth()->claims(['role' => $role])->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token, $user->id);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        //  $user = User::find('id', auth()->user->id);
        return $this->respondWithToken(auth()->refresh());
    }

    public function register(Request $request)
    {
        // $credentials = request(['email', 'password']);
        // $user = new User($credentials);
        // $user->save();
        // return $user;
        //return request()->all();
        $credentials = $request->validate([
            'email' => 'required|unique:users',
            'password' => 'required|min:6',
            'firstName' => 'required',
            'lastName' => 'required',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = new User($credentials);
        $user->setPassword($request['password']);
        $user->save();

        $userRole = UserRole::create([
            'user_id' => $user->id,
            'role_id' => $request->role_id
        ]);
        return $user;
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        //  $user = User::where('id', $id)->first();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60 * 60 * 24 * 10000
            // 'user' => $user
        ]);
    }
}
