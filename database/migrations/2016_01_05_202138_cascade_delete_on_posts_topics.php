<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CascadeDeleteOnPostsTopics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table( 'posts_topics', function ( $table ) {
        $table->dropForeign( 'posts_topics_post_id_foreign' );
        $table->dropForeign( 'posts_topics_topic_id_foreign' );

        $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' )->onDelete( 'cascade' );
        $table->foreign( 'topic_id' )->references( 'id' )->on( 'topics' )->onDelete( 'cascade' );;
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table( 'posts_topics', function ( $table ) {
        $table->dropForeign( 'posts_topics_post_id_foreign' );
        $table->dropForeign( 'posts_topics_topic_id_foreign' );

        $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' );
        $table->foreign( 'topic_id' )->references( 'id' )->on( 'topics' );
      });
    }
}
