<?php

  namespace DolphinApi\Repositories;

  use DB;

  use DolphinApi\Topic;

  class TopicRepository
  {
    static function filter( $filterData )
    {
      $query = DB::table( 'topics' );

      $sortBy = isset( $filterData['sort_by'] ) && $filterData['sort_by'] ? $filterData['sort_by'] : 'created_at';

      $selectFields = ['topics.id', 'topics.name'];
      if ( $sortBy == 'posts_count' ) {
        $selectFields[] = DB::raw( 'COUNT( DISTINCT( posts_topics.post_id ) ) AS posts_count' );
      }
      $query->select( $selectFields );

      if ( $sortBy == 'posts_count' ) {
        $query->leftJoin('posts_topics', 'topics.id', '=', 'posts_topics.topic_id');
      }

      if ( isset( $filterData['pattern'] ) && $filterData['pattern'] ) {
        $pattern = strtolower( $filterData['pattern'] );
        $query->whereRaw(
          'LOWER( topics.name ) LIKE ?',
          ["%$pattern%"]
        ); // TODO: fulltext search?
      }

      $query->groupBy( 'topics.id' );
      $query->orderBy( $sortBy, 'desc' );

      $quantity = isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10;
      if ( isset( $filterData['page'] ) ) {
        $offset = $filterData['page'] * $quantity;
        $query->limit( $quantity )->offset( $offset );
      }
      else {
        $query->take( ( isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10 ) );
      }

      $topics = $query->get();

      return $topics;
    }

    static function byUserId( $userId )
    {
      $query = DB::table( 'topics' );
      $query->select( DB::raw( 'DISTINCT( topic_id )' ), 'name' );

      $query->join( 'posts',        'posts.user_id',  '=', DB::raw( $userId ) );
      $query->join( 'posts_topics', 'posts.id',       '=', 'posts_topics.post_id' );

      $query->groupBy( 'topic_id' );

      $topics = $query->get();

      return $topics;
    }
  }