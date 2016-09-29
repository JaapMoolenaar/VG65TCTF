<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWidthHeightToImages extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table( 'images', function ( Blueprint $table ) {
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
    Schema::table( 'images', function ( Blueprint $table ) {
      $table->dropColumn( 'image_width' );
      $table->dropColumn( 'image_height' );
    });
  }
}
