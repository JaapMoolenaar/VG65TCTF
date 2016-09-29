<?php

namespace DolphinApi;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'username', 'is_private', 'location', 'email', 'password', 'device_id', 'device_token', 'avatar_image_url', 'gender', 'city', 'country', 'zip'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'device_token'];
    protected $visible = ['id', 'first_name', 'last_name', 'username', 'is_private', 'email', 'location', 'gender', 'city', 'country', 'zip', 'avatar_image_url', 'grades', 'subjects', 'pods', 'roles'];

    public function getCreatedAtAttribute( $value )
    {
        return date( 'U', strtotime( $value ) );
    }

    public function posts()
    {
      return $this->hasMany( 'DolphinApi\Post' );
    }

    public function roles()
    {
        return $this->hasMany( 'DolphinApi\Role' );
    }

    public function likes()
    {
      return $this->hasMany( 'DolphinApi\Like' );
    }

    public function reports()
    {
        return $this->hasMany( 'DolphinApi\AbuseReport' );
    }

    public function pods()
    {
      return $this->belongsToMany( 'DolphinApi\Pod', 'pods_users' );
    }

    public function grades()
    {
      return $this->belongsToMany( 'DolphinApi\Grade', 'users_grades' );
    }

    public function subjects()
    {
        return $this->belongsToMany( 'DolphinApi\Subject', 'users_subjects' );
    }

    public function comments()
    {
      return $this->hasMany( 'DolphinApi\Comment' );
    }
        
    public function getFullName()
    {
      return (
        $this->first_name && $this->last_name ? 
          $this->first_name.' '.$this->last_name : 
          $this->username
      );
    }
    
    public function getFirstName()
    {
      return (
        $this->first_name ? 
          $this->first_name : 
          $this->username
      );
    }
}

