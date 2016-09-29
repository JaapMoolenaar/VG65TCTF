<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
  protected $table = 'users_roles';

  public function getCreatedAtAttribute( $value )
  {
    return date( 'U', strtotime( $value ) );
  }
}