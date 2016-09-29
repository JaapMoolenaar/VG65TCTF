<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class PostOpen extends Model
{
  protected $visible = ['user', 'post', 'id', 'created_at'];
  protected $fillable = ['post_id', 'user_id'];
  protected $table = 'post_opens';

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
