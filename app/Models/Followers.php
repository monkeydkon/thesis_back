<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Followers extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'follower_id'];
    // public function followers(){
    //     return $this->hasMany(User::class, 'follower_id');
    // }
    public function following(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
