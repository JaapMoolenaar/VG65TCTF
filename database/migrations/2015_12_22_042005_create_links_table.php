<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinksTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create( 'links', function ( Blueprint $table ) {
      $table->text( 'url' );
      $table->integer( 'post_id', false, true );
      $table->timestamps();

      $table->increments('id');
      $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' );
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
      Schema::drop('links');
  }
}
