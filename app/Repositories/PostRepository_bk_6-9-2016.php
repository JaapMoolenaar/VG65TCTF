<?php

  namespace DolphinApi\Repositories;

  use DB;

  use DolphinApi\Post;

  class PostRepository
  {

    static function filter( $filterData, $loggedUserId = 0 )
    {
      $query = DB::table( 'posts' );

      $selectFields = ['posts.id'];

      if ( isset( $filterData['sort_by'] ) && $filterData['sort_by'] == 'likes_count' ) {
        $selectFields[] = DB::raw( 'COUNT( DISTINCT( likes.id ) ) AS  likes_count' );
        $query->leftJoin( 'likes', 'posts.id', '=', 'likes.post_id' );
        $query->orderBy( 'likes_count', 'desc' );
      }

      $query->select( $selectFields );

      if ( isset( $filterData['subjects'] ) && $filterData['subjects'] ) {
        $query->join( 'users_subjects', 'posts.user_id', '=', 'users_subjects.user_id' );
        $query->join( 'subjects', 'subjects.id', '=', 'users_subjects.subject_id' );
        $query->whereIn( 'subjects.id', $filterData['subjects'] );
      }

      if ( isset( $filterData['grades'] ) && $filterData['grades'] ) {
        $query->join( 'users_grades', 'posts.user_id', '=', 'users_grades.user_id' );
        $query->join( 'grades', 'grades.id', '=', 'users_grades.grade_id' );
        $query->whereIn( 'grades.id', $filterData['grades'] );
      }

      if ( isset( $filterData['topics'] ) && $filterData['topics'] ) {
        $query->join( 'posts_topics', 'posts.id', '=', 'posts_topics.post_id' );
        $query->join( 'topics', 'topics.id', '=', 'posts_topics.topic_id' );
        $query->whereIn( 'topics.name', $filterData['topics'] );
      }

      if ( isset( $filterData['types'] ) && $filterData['types'] ) {
        $query->join( 'post_types', 'posts.post_type_id', '=', 'post_types.id' );
        $query->whereIn( 'post_types.name', $filterData['types'] );
      }

      if ( isset( $filterData['user_id'] ) && $filterData['user_id'] ) {
        $query->where( 'posts.user_id', "=", $filterData['user_id'] );
      }

      if ( isset( $filterData['pod_id'] ) && $filterData['pod_id'] ) {
        $query->where( 'posts.pod_id', "=", $filterData['pod_id'] );
      }

      if ( isset( $filterData['from_date'] ) && $filterData['from_date'] ) {
        $query->where( 'posts.created_at', ">=", $filterData['from_date'] );
      }

      if ( isset( $filterData['to_date'] ) && $filterData['to_date'] ) {
        $query->where( 'posts.created_at', "<=", $filterData['to_date'] );
      }

      $query->groupBy( 'posts.id' );
      $query->orderBy( 'posts.created_at', 'desc' );

      $quantity = isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10;
      if ( isset( $filterData['page'] ) ) {
        $offset = $filterData['page'] * $quantity;
        $query->limit( $quantity )->offset( $offset );
      }
      else {
        $query->take( ( isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10 ) );
      }

      $postIds = $query->get();

      $posts = [];
      foreach( $postIds as $postId ) {
        $post = Post::where( 'id', $postId->id )->first();
        $posts[] = self::supercharge( $post, $loggedUserId );
      }
      return $posts;
    }

    static function supercharge( &$posts, $loggedUserId = 0 )
    {
      if ( !is_array( $posts ) ) {
        $posts = [$posts];
      }

      foreach( $posts as &$post ) {
        $post->setAttribute( 'user', $post->user()->first() );
        $post->setAttribute( 'type', $post->postType()->first() );
        $post->setAttribute( 'topics', $post->topics()->get() );

        if ( $postImage = $post->image()->first() ) {
          $post->setAttribute( 'image', $postImage );
        }
        elseif ( $postLink  = $post->link()->first() ) {
          $post->setAttribute( 'link', $postLink );
        }

        $post->setAttribute( 'likes_count', $post->likes()->count() );
        $post->setAttribute( 'comments_count', $post->comments()->count() );

        $post->setAttribute( 'is_liked', $post->likes()->where( 'user_id', $loggedUserId )->count()  );
      }

      return $posts;
    }

  }