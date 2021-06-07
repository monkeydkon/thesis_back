<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Followers;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function findByEmail(Request $request)
    {
        $email = $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'user not found'], 500);
        }
        return $user;
    }

    public function findUser($id)
    {
        return User::with('blogs')->where('id', $id)->firstOrFail();
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
