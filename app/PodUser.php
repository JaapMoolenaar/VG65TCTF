<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class PodUser extends Model
{
  protected $table = 'pods_users';

  protected $fillable = ['user_id', 'pod_id', 'is_owner', 'is_approved', 'invite_token'];
  protected $visible  = ['user_id', 'pod_id', 'is_owner', 'is_approved', 'invite_token'];

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }
  
  function user()
  {
    return $this->belongsTo( 'DolphinApi\User', 'user_id' );
  }
}