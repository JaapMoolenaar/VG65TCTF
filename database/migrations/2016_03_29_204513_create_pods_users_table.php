<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePodsUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create( 'pods_users', function ( Blueprint $table ) {
      $table->increments( 'id' );

      $table->integer( 'user_id', false, true );
      $table->integer( 'pod_id',  false, true );
      $table->boolean( 'is_approved' );
      $table->boolean( 'is_owner' );
      $table->timestamps();

      $table->foreign( 'user_id' )->references( 'id' )->on( 'users' )->onDelete( 'cascade' );
      $table->foreign( 'pod_id' )->references( 'id' )->on( 'pods' )->onDelete( 'cascade' );
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop( 'pods_users' );
  }
}
