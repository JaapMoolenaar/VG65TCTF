<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUsersSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_subjects', function ( Blueprint $table ) {
            $table->integer( 'user_id', false, true );
            $table->integer( 'subject_id', false, true );
            $table->timestamps();

            $table->increments( 'id' );
            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' );
            $table->foreign( 'subject_id' )->references( 'id' )->on( 'subjects' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop( 'users_subjects' );
    }
}
