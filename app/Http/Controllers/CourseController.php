<?php

namespace App\Http\Controllers;

use App\Models\Assignments;
use App\Models\Chat;
use App\Models\Course;
use App\Models\CourseMembers;
use App\Models\CoursePosts;
use App\Models\CourseStudentPosts;
use App\Models\File;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Role;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getClasses()
    {
        $roleName = Role::where('id', auth()->user()->role->role_id)->first()->name;

        if ($roleName == 'teacher') {
            $courses = Course::with('teacher', 'files', 'posts', 'studentPosts.user', 'members.user', 'assignments')->where('user_id', auth()->user()->id)->get();
            return $courses;
        } else if ($roleName == 'student') {
            $courseMembers = CourseMembers::all()->where('user_id', auth()->user()->id);
            $res = [];
            foreach ($courseMembers as $courseMember) {
                $course = Course::with('teacher', 'files', 'posts', 'studentPosts.user', 'members.user', 'assignments')->where('id', $courseMember->course->id)->first();
                array_push($res, $course);
            }
            return $res;
        }
    }

    public function createClass(Request $request)
    {
        $class = new Course($request->validate([
            'name' => 'required'
        ]));
        $class->user_id = auth()->user()->id;
        return $class->save();
    }

    public function search($course_id, $query)
    {
        $query = strtolower($query);

        $studentPosts = CourseStudentPosts::query()->with('user')
            ->where('course_id', $course_id)
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->get();

        $posts = CoursePosts::query()
            ->where('course_id', $course_id)
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->get();

        $members = CourseMembers::query()->with('user')->where('course_id', $course_id)
            ->whereHas('user', function ($q) use ($query) {
                $q->where('firstName', 'LIKE', "%{$query}%");
            })
            ->orWhereHas('user', function ($q) use ($query) {
                $q->where('lastName', 'LIKE', "%{$query}%");
            })
            ->get();

        $files = File::query()
            ->where('course_id', $course_id)
            ->where('name', 'LIKE', "%{$query}%")
            ->get();

        $assignments = Assignments::query()
            ->where('course_id', $course_id)
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "{$query}")
            ->get();

        return [
            'studentPosts' => $studentPosts,
            'posts' => $posts,
            'members' => $members,
            'files' => $files,
            'assignments' => $assignments
        ];
    }

    public function addUserToClass($course_id, Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $course = Course::find($course_id);
        if (!$course) {
            return response()->json(['message' => 'course not found'], 500);
        }

        if ($course->user_id != auth()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        CourseMembers::create([
            'course_id' => $course->id,
            'user_id' => $request->user_id
        ]);


        Chat::create([
            'course_id' => $course->id,
            'student_id' => $request->user_id
        ]);
        return;
    }

    public function deleteUserFromClass($course_id, $user_id)
    {
        $course_member = CourseMembers::where('course_id', $course_id)->where('user_id', $user_id)->first();
      //  return $course_member;
        $course_member->delete();
        return;
    }

    public function getChats($course_id, Request $request)
    {

        $page = $request->query('page');
        $course = Course::where('id', $course_id)->firstOrfail();
        //   return Chat::with('messages.user')->where('course_id', $course->id)->where('student_id', auth()->user()->id)->first();
        return Chat::with(['messages' => function ($q) use ($page) {
            $q->latest()->take(5 * $page)->get();
        }])->withCount('messages')->where('course_id', $course->id)->where('student_id', auth()->user()->id)->first();
        //  return $course_id;
    }

    public function getTeacherChat($course_id, $user_id, Request $request)
    {
        $page = $request->query('page');
        $course = Course::where('id', $course_id)->firstOrfail();

        return Chat::with(['messages' => function ($q) use ($page) {
            $q->latest()->take(5 * $page)->get();
        }])->withCount('messages')->where('course_id', $course->id)->where('student_id', $user_id)->first();
    }

    public function getUsers($course_id)
    {
        $course = Course::find($course_id);
        if ($course->user_id != auth()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $students = Chat::with('student')->where('course_id', $course_id)->get()->pluck('student');
        return $students;
    }

    public function postMessage($course_id, $chat_id, Request $request)
    {
        $request->validate([
            'message' => 'required'
        ]);

        $message = new Message();
        $message->message = $request->message;
        $message->user_id = auth()->user()->id;
        $message->chat_id = $chat_id;
        $message->save();
        return $message;
    }
}
