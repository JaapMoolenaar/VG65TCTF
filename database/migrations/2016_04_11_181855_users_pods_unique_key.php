<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersPodsUniqueKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table( 'pods_users', function ( Blueprint $table ) {
        $table->unique( ['user_id', 'pod_id'], 'pods_users_pod_id_user_id_unique' );
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table( 'pods_users', function ( Blueprint $table ) {
        $table->dropUnique( 'pods_users_pod_id_user_id_unique' );
      });
    }
}
