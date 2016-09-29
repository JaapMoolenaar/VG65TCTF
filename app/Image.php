<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
  protected $fillable = ['image_url', 'image_width', 'image_height'];
  protected $visible  = ['id', 'image_url', 'image_width', 'image_height'];

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }

  function post()
  {
    $this->belongsTo( 'DolphinApi\Post' );
  }
}