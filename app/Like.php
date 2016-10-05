<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
  protected $visible = ['user', 'post', 'comment', 'id', 'created_at'];

  protected $fillable = ['post_id', 'comment_id', 'user_id'];

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
  
  public function comment()
  {
    return $this->belongsTo( 'DolphinApi\Comment', 'comment_id' );
  }
}
