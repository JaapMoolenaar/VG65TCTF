<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUsersGrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'users_grades', function ( Blueprint $table ) {
            $table->integer( 'user_id', false, true );
            $table->integer( 'grade_id', false, true );
            $table->timestamps();

            $table->increments( 'id' );
            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' );
            $table->foreign( 'grade_id' )->references( 'id' )->on( 'grades' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop( 'users_grades' );
    }
}
