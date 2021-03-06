<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAnswers;
use App\Models\Assignments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentsController extends Controller
{
    public function newAssignment(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'end_date' => 'required|date',
            'course_id' => 'required|exists:courses,id'
        ]);

        $assignment = new Assignments();
        $assignment->title = $request->title;
        $assignment->description = $request->description;
        $assignment->end_date = $request->end_date;
        $assignment->course_id = $request->course_id;

        $file = $request->file('file');

        if ($file) {
            $path = Storage::disk('local')->putFile('assignments', $file);
            $filename = $file->getClientOriginalName();
            $assignment->path = $path;
            $assignment->name = $filename;
        }

        $assignment->save();
        return $assignment;
    }

    public function submitAssignment(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'file' => 'required'
        ]);

        $answer = new AssignmentAnswers();
        $answer->user_id = auth()->user()->id;
        $answer->assignment_id = $request->assignment_id;
        
        $file = $request->file('file');

        if($file){
            $path = Storage::disk('local')->putFile('answers', $file);
            $answer->path = $path;
        }
        $answer->save();
        return $answer;
    }

    public function download($id)
    {
        $assignment = Assignments::find($id);
        return Storage::download($assignment->path);
    }
    
    public function downloadAnswer($id)
    {
        $answer = AssignmentAnswers::find($id);
        return Storage::download($answer->path);
    }

    public function deleteAssignment($id)
    {
        $assignment = Assignments::find($id);
        if (!$assignment) {
            return response(['message' => 'not found'], 404);
        }

        if ($assignment->course->user_id != auth()->user()->id) {
            return response(['message' => 'unauthorized'], 403);
        }

        Storage::disk('local')->delete($assignment->path);
        $assignment->delete();
    }
}
