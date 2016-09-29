<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnAvatarImageToAvatarImageUrlInUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table( 'users', function ( Blueprint $table ) {
        $table->renameColumn( 'avatar_image', 'avatar_image_url' );
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
        $table->renameColumn( 'avatar_image_url', 'avatar_image' );
      });
    }
}
