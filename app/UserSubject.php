<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class UserSubject extends Model
{
  protected $table = 'users_subjects';

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }
}