<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CascadePostLinkOnDelete extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table( 'links', function ( Blueprint $table ) {
      $table->dropForeign( 'links_post_id_foreign' );

      $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' )->onDelete( 'cascade' );
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
      $table->dropForeign( 'links_post_id_foreign' );

      $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' );
    });
  }
}
