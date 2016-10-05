<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

  protected $fillable = ['post_id', 'user_id', 'body', 'type'];
  protected $visible = ['id', 'user', 'post', 'body', 'created_at', 'likes_count', 'is_liked', 'image', 'link', 'type'];

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

  function likes()
  {
    return $this->hasMany( 'DolphinApi\Like', 'comment_id' );
  }

  function link()
  {
    return $this->hasOne( 'DolphinApi\Link', 'comment_id' );
  }

  function image()
  {
    return $this->hasOne( 'DolphinApi\Image', 'comment_id' );
  }
}
