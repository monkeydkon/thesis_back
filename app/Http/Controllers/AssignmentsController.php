<?php

namespace App\Http\Controllers;

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
            $assignment->path = $path;
        }

        $assignment->save();
        return $assignment;
    }

    public function download($id)
    {
        $assignment = Assignments::find($id);
        return Storage::download($assignment->path);
    }
}
