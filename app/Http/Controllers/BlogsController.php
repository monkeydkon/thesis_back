<?php

namespace App\Http\Controllers;

use App\Models\BlogComments;
use App\Models\Blogs;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogsController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    public function getBlogs()
    {
        $blogs = Blogs::with('comments.user')->where('user_id', auth()->user()->id)->latest()->get();

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

        //    return $blog;
        $blog->save();
        return $blog;
    }

    public function getImage($blog_id)
    {
        $blog = Blogs::find($blog_id);
        if (!$blog->img_path) {
            return;
        }

        return Storage::download($blog->img_path);
        // return Storage::disk('local')->path($blog->img_path);
        //  return Storage::url($blog->img_path);
        // // return Storage::download($blog->img_path);
        
        // return Storage::url($blog->img_path);
    }

    public function addComment($id, Request $request)
    {
        $request->validate([
            'comment' => 'required'
        ]);

        $blog = Blogs::findOrFail($id);
        $blog_comment = new BlogComments(['comment' => $request->comment, 'user_id' => auth()->user()->id]);
        $blog->comments()->save($blog_comment);

        return  $blog_comment->load('user'); // $blog->load('comments.user');
    }

    public function addImage($blog_id, Request $request)
    {
        $image = $request->file('image');

        $path = Storage::disk('local')->putFile('blogs', $image);

        $blog = Blogs::find($blog_id);
        $blog->img_path = $path;
        return $blog->save();
    }

    public function deleteComment($blog_id, $comment_id)
    {
        $blog = Blogs::find($blog_id);

        if ($blog->user_id != auth()->user()->id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $comment = BlogComments::find($comment_id);
        if (!$comment) {
            return response()->json(['message' => 'no such comment'], 404);
        }

        return $comment->delete();
    }

    public function deleteBlog($id)
    {
        $blog = Blogs::find($id);

        if (!$blog) {
            return response()->json(['message' => 'no such blog'], 404);
        }

        if ($blog->user_id != auth()->user()->id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        return $blog->delete();
        
    }
}
