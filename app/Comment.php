<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
  protected $visible = ['id', 'user', 'post', 'body', 'created_at'];

  protected $fillable = ['post_id', 'user_id', 'body'];

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }

  function user()
  {
    return $this->belongsTo( 'DolphinApi\User' );
  }

  function post()
  {
    return $this->belongsTo( 'DolphinApi\Post' );
  }

}
