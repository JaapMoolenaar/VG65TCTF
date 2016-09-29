<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUniqueDeviceIdFromUsers extends Migration
{
    public function up()
    {
        Schema::table( 'users', function ( Blueprint $table ) {
            $table->dropUnique( 'users_device_id_unique' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'users', function ( Blueprint $table ) {
            $table->unique( 'device_id', 'users_device_id_unique' );
        });
    }
}
