<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLikesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create( 'likes', function ( Blueprint $table ) {
      $table->integer( 'post_id', false, true );
      $table->integer( 'user_id', false, true );
      $table->timestamps();

      $table->increments( 'id' );
      $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' );
      $table->foreign( 'user_id' )->references( 'id' )->on( 'users' );
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('likes');
  }
}
