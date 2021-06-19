<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Followers;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function findByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required'
        ]);

        $user = User::where('email', 'LIKE', "%{$request->email}%")->get();

        if (!$user) {
            return response()->json(['message' => 'user not found'], 500);
        }

        $role_id = Role::where('name', 'student')->first()->id;
     //   return $user;
        // return $user->filter(function ($item) use ($role_id) {
        //     return $item->role->role_id == $role_id;
        // });

        return $user;

        $user = $user->filter(function ($item) use ($role_id) {
            return $item->role->role_id == $role_id;
        });

        $xaxa = [];
        // $user->each(function($item) use($xaxa){
        // //    $response->xaxa($item);
        //     array_push($xaxa, $item);
        // });
        // for($user as $lol){
        //     array_push($xaxa, $item);
        // }

        return $xaxa;
        //return $user;
    }

    public function findUser($id)
    {
        return User::with('blogs.comments.user')->where('id', $id)->firstOrFail();
    }

    public function searchTeacher(Request $request)
    {
        $search = strtolower($request->query('search'));
        $teachers = User::with('role')->where('email', '!=', auth()->user()->email)
            ->where(function ($q) use ($search) {
                $q->where('firstName', 'LIKE', "%{$search}%")
                    ->orWhere('lastName', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->get();

        $role_id = Role::where('name', 'teacher')->first()->id;

        return $teachers->filter(function ($item) use ($role_id) {
            return $item->role->role_id == $role_id;
        });
    }

    public function updateProfile(Request $request)
    {

        $request->validate([
            'linkedin' => 'nullable|url',
            'github' => 'nullable|url'
        ]);

        $user = User::where('id', auth()->user()->id)->first();

        $user->summary = $request->summary;
        $user->linkedin = $request->linkedin;
        $user->github = $request->github;

        $user->save();

        return $user;
    }

    public function follow($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'no such user'], 500);
        }

        $follower = new Followers([
            'user_id' => $id,
            'follower_id' => auth()->user()->id
        ]);
        return $follower->save();
    }

    public function unfollow($id)
    {
        $following = Followers::where('user_id', $id)->where('follower_id', auth()->user()->id);
        $following->delete();

        return;
    }

    public function getFollowStatus($id)
    {
        if (Followers::where('user_id', $id)->where('follower_id', auth()->user()->id)->exists()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getUsersFollowing()
    {
        //$following = Followers
        $following = Followers::with('following')->where('follower_id', auth()->user()->id)->get();
        return $following;
    }
}
