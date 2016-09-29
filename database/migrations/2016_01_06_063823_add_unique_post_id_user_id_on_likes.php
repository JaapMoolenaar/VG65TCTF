<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniquePostIdUserIdOnLikes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('likes', function (Blueprint $table) {
        $table->unique( ['user_id', 'post_id'], 'likes_user_id_post_id_unique' );
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table( 'likes', function ( Blueprint $table ) {
        $table->dropUnique( 'likes_user_id_post_id_unique' );
      });
    }
}
