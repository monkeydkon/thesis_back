<?php

namespace App\Http\Controllers;

use App\Models\Blogs;
use Illuminate\Http\Request;

class BlogsController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    public function getBlogs()
    {
       // return [];
        $blogs = Blogs::where('user_id', auth()->user()->id)->latest()->get();
        // if(!$blogs){
        //     return []
        // }
        return $blogs;
    }

    public function createBlog(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required'
        ]);

        $blog = new Blogs();
        $blog->title = $request->title;
        $blog->content = $request->content;
        $blog->user_id = auth()->user()->id;

        $blog->save();

        return $blog;
    }
}
