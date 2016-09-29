<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewUserFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'users', function ( $table ) {
            $table->string( 'username', 255 );
            $table->string( 'first_name', 255 );
            $table->string( 'last_name', 255 );
            $table->string( 'location', 255 );
            $table->boolean( 'is_private' )->default( false );

            $table->unique( 'username' );
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'users', function ( $table ) {
            $table->dropColumn( 'username' );
            $table->dropColumn( 'first_name' );
            $table->dropColumn( 'last_name' );
            $table->dropColumn( 'location' );
            $table->dropColumn( 'is_private' );
        });
    }
}
