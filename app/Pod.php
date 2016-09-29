<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;
use DolphinApi\Read;

class Pod extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['name', 'description', 'is_private', 'image_width', 'image_height', 'approval_token', 'total_unread'];
  protected $visible  = ['id', 'name', 'description', 'image_url', 'is_private', 'users', 'owner', 'posts_count', 'users_count', 'last_post', 'image_width', 'image_height', 'approval_token', 'total_unread', 'is_owner', 'is_member'];
  
  public $total_unread;

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }

  public function users()
  {
    return $this->belongsToMany( 'DolphinApi\User', 'pods_users' );
  }

  public function posts()
  {
    return $this->hasMany( 'DolphinApi\Post' );
  }
  
  public static function getTotalPostRead($pod, $userId){
	  $readCount = Read::where('pod_id' , $pod->id)
	  ->where('user_id', $userId)
	  ->count();
	  return $readCount;
  }

}
