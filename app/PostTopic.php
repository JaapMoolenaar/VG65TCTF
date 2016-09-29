<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class PostTopic extends Model
{
  protected $table = 'posts_topics';

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }
}
