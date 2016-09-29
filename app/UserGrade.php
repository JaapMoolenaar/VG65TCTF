<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class UserGrade extends Model
{
  protected $table = 'users_grades';

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }

}