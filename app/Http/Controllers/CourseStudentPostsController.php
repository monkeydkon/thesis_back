<?php

namespace App\Http\Controllers;

use App\Models\CourseStudentPosts;
use Illuminate\Http\Request;

class CourseStudentPostsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function createPost(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'content' => 'required',
            'course_id' => 'required|exists:courses,id'
        ]);
        $post = new CourseStudentPosts();
        $post->title = $data['title'];
        $post->content = $data['content'];
        $post->course_id = $data['course_id'];
        $post->user_id = auth()->user()->id;

        //  return $post;
        $post->save();
        return $post;
    }
}
