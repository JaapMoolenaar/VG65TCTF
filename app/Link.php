<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
  protected $visible = ['id', 'url', 'image_url', 'image_width', 'image_height'];

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }

  function post()
  {
    $this->belongsTo( 'DolphinApi\Post' );
  }
}