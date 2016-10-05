<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
  protected $visible = ['notification_id', 'type', 'object_id', 'user_id', 'is_read', 'created_at', 'updated_at', 'receiver_id', 'post_id', 'comment_id', 'pod_id', 'user', 'post', 'pod'];
  protected $fillable = ['type', 'object_id', 'user_id', 'is_read', 'receiver_id', 'post_id', 'pod_id'];
  protected $table = 'notifications';

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }

  public function user()
  {
    return $this->belongsTo( 'DolphinApi\User' );
  }

  public function post()
  {
    return $this->belongsTo( 'DolphinApi\Post' );
  }

  public function pod()
  {
    return $this->belongsTo( 'DolphinApi\Pod' );
  }
}
