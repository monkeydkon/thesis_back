<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable  implements JWTSubject
{
    use Notifiable;

    // Rest omitted for brevity
    protected $fillable = ['firstName', 'lastName', 'email'];

    protected $hidden = ['password'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role
        ];
    }

    public function role()
    {
        return $this->hasOne(UserRole::class);
    }

    public function setPassword($password)
    {
        $this->password = bcrypt($password);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function studentPosts()
    {
        return $this->hasMany(CourseStudentPosts::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blogs::class);
    }

    public function following(){
        return $this->belongsTo(Followers::class, 'user_id');
    }

    // public function following(){
    //     return $this->hasMany(Followers::class, 'user_id');
    // }
}
