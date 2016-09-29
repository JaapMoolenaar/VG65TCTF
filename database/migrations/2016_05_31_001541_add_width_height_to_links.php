<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWidthHeightToLinks extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table( 'links', function ( Blueprint $table ) {
      $table->integer( 'image_width',  false, true );
      $table->integer( 'image_height', false, true );
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table( 'links', function ( Blueprint $table ) {
      $table->dropColumn( 'image_width' );
      $table->dropColumn( 'image_height' );
    });
  }
}