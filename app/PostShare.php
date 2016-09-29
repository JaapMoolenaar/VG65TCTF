<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class PostShare extends Model
{
  protected $visible = ['user', 'post', 'id', 'created_at'];
  protected $fillable = ['post_id', 'user_id', 'type'];

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
}
