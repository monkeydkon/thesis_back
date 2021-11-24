<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function teacher(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function files(){
        return $this->hasMany(File::class);
    }

    public function members(){
        return $this->hasMany(CourseMembers::class);
    }

    public function posts(){
        return $this->hasMany(CoursePosts::class);
    }

    public function studentPosts(){
        return $this->hasMany(CourseStudentPosts::class);
    }

    public function assignments(){
        return $this->hasMany(Assignments::class);
    }

    public function chats(){
        return $this->hasMany(Chat::class);
    }
}
