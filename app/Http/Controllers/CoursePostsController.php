<?php

namespace App\Http\Controllers;

use App\Models\CoursePosts;
use Illuminate\Http\Request;

class CoursePostsController extends Controller
{
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
        $post = new CoursePosts();
        $post->title = $data['title'];
        $post->content = $data['content'];
        $post->course_id = $data['course_id'];

        //  return $post;
        $post->save();
        return $post;
    }

    public function deletePost($id)
    {
        $post = CoursePosts::find($id);
        return $post->delete();
    }
}
