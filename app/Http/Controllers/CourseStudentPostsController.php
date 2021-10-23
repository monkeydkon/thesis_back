<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudentPosts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;

class CourseStudentPostsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function createPost(Request $request)
    {

        $course = Course::find($request->course_id);
        Logger( $course->id);
      //    $this->authorize('xusi', [auth()->user(),$course]);
       // if (auth()->user()->can('create', [auth()->user(), $course])) {
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
        // }else{
        //     Logger('lol');
        // }
    }

    public function deletePost($id)
    {
        $post = CourseStudentPosts::find($id);
        return $post->delete();
    }
}
