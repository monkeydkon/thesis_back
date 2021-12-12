<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class FileController extends Controller
{
    public function newFile(Request $request)
    {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $course_id = $request->course_id;

        $path = Storage::disk('local')->putFile('files', $file);

        $new_file = new File([
            'path' => $path,
            'name' => $filename,
            'course_id' => $course_id
        ]);
        $new_file->save();
        return $new_file;
    }

    public function download(Request $request)
    {
        // return 'xa';
      //  $this->validate($request, ['id' => 'required|exists:files,id']);
        // $request->merge(['id' => $request->id]);
        // $data = $request->validate([
        //     'id' => 'required|exists:files,id'
        // ]);
        $file = File::find($request->id);
        if(!$file){
             return response()->json(['message' => 'file not found'], 500);
        }

        return Storage::download($file->path);
        return $file;
    }

    public function deleteFile($id)
    {
        $file = File::find($id);
        if(!$file){
            return response(['message' => 'not found'],404);
        }

        if($file->course->user_id != auth()->user()->id){
            return response(['message' => 'unauthorized'],403);
        }

        Storage::disk('local')->delete($file->path);
        $file->delete();
    }
}
