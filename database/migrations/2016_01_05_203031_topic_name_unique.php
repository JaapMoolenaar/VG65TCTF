<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TopicNameUnique extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table( 'topics', function ( $table ) {
      $table->unique( 'name' );
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table( 'topics', function ( $table ) {
      $table->dropUnique( 'topics_name_unique' );
    });
  }
}
