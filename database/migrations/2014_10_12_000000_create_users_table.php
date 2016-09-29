<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create( 'users', function ( Blueprint $table ) {
      $table->string( 'name' );
      $table->string( 'avatar_image' );
      $table->string( 'email' )->unique();
      $table->string( 'password', 255 );
      $table->string( 'device_id', 255 );
      $table->string( 'api_token', 255 );
      $table->rememberToken();
      $table->timestamps();

      $table->increments( 'id' );
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('users');
  }
}
