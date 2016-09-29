<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'posts_topics', function ( Blueprint $table) {
          $table->integer( 'post_id', false, true );
          $table->integer( 'topic_id', false, true );
          $table->timestamps();

          $table->increments( 'id' );
          $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' );
          $table->foreign( 'topic_id' )->references( 'id' )->on( 'topics' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('posts_topics');
    }
}
