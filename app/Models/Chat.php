<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'student_id'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
