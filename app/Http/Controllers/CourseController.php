<?php

namespace App\Http\Controllers;

use App\Models\Assignments;
use App\Models\Course;
use App\Models\CourseMembers;
use App\Models\CoursePosts;
use App\Models\CourseStudentPosts;
use App\Models\File;
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
}
