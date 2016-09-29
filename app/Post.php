<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['title', 'body', 'post_type_id', 'user_id', 'pod_id'];
  protected $visible  = ['id', 'title', 'body', 'image', 'link', 'type', 'user', 'topics', 'created_at', 'likes_count', 'comments_count', 'is_liked', 'pod'];

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }

  function postType()
  {
    return $this->belongsTo( 'DolphinApi\PostType' );
  }

  function pod()
  {
    return $this->belongsTo( 'DolphinApi\Pod' );
  }

  function link()
  {
    return $this->hasOne( 'DolphinApi\Link' );
  }

  function image()
  {
    return $this->hasOne( 'DolphinApi\Image' );
  }

  function user()
  {
    return $this->belongsTo( 'DolphinApi\User' );
  }

  function comments()
  {
    return $this->hasMany( 'DolphinApi\Comment' );
  }

  function likes()
  {
    return $this->hasMany( 'DolphinApi\Like' );
  }

  function reports()
  {
    return $this->hasMany( 'DolphinApi\AbuseReport' );
  }

  function topics()
  {
    return $this->belongsToMany( 'DolphinApi\Topic', 'posts_topics' );
  }

}
