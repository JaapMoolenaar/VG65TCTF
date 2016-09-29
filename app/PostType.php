<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class PostType extends Model
{
  protected $visible = ['id', 'name'];

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }

}
