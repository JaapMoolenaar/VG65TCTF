<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbuseReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create( 'abuse_reports', function ( Blueprint $table ) {
        $table->increments( 'id' );

        $table->integer( 'user_id', false, true );
        $table->integer( 'post_id', false, true );
        $table->timestamps();

        $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' );
        $table->foreign( 'user_id' )->references( 'id' )->on( 'users' );
        $table->unique( ['user_id', 'post_id'], 'reports_user_id_post_id_unique' );
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::drop( 'abuse_reports' );
    }
}
