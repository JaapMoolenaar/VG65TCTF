<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create( 'posts', function ( Blueprint $table ) {
      $table->string( 'title' );
      $table->text( 'body' );
      $table->integer( 'user_id', false, true );
      $table->integer( 'post_type_id', false, true );
      $table->timestamps();

      $table->increments('id');
      $table->foreign( 'user_id' )->references( 'id' )->on( 'users' )->onDelete( 'cascade' );
      $table->foreign( 'post_type_id' )->references( 'id' )->on( 'post_types' );
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
      Schema::drop( 'posts' );
  }
}
