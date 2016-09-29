<?php

  namespace DolphinApi\Repositories;

  use DB;

  use DolphinApi\Pod;
  use DolphinApi\Read;

  class PodRepository
  {

    static function filter( $filterData, $loggedUserId = 0 )
    { // TODO: optimize
      $query = DB::table( 'pods' );

      $sortBy = isset( $filterData['sort_by'] ) && $filterData['sort_by'] ? $filterData['sort_by'] : 'pods.created_at';

      $selectFields = ['pods.id'];
      if ( $sortBy == 'users_count' ) {
        $selectFields[] = DB::raw( 'COUNT( DISTINCT( pods_users.user_id ) ) AS  users_count' );
      }
      $query->select( $selectFields );

      if ( isset( $filterData['pattern'] ) && $filterData['pattern'] ) {
        $pattern = strtolower( $filterData['pattern'] );
        $query->whereRaw(
          '( LOWER( pods.name )        LIKE ? OR
             LOWER( pods.description ) LIKE ? )',
          ["%$pattern%", "%$pattern%"]
        ); // TODO: fulltext search?
      }

      if ( isset( $filterData['user_id'] ) && $filterData['user_id'] || $sortBy == 'users_count' ) {
        $query->leftJoin( 'pods_users', 'pods.id', '=', 'pods_users.pod_id' );
        $query->leftJoin( 'users', 'users.id', '=', 'pods_users.user_id' );
      }
      if ( isset( $filterData['user_id'] ) && $filterData['user_id'] ) {
        $query->where( 'users.id', $filterData['user_id'] );
      }

      if ( isset( $filterData['is_private'] ) && $filterData['is_private'] ) {
        $query->where( 'pods.is_private', "=", $filterData['is_private'] );
      }

      if ( isset( $filterData['from_date'] ) && $filterData['from_date'] ) {
        $query->where( 'pods.created_at', ">=", $filterData['from_date'] );
      }

      if ( isset( $filterData['to_date'] ) && $filterData['to_date'] ) {
        $query->where( 'pods.created_at', "<=", $filterData['to_date'] );
      }

      $query->groupBy( 'pods.id' );
      $query->orderBy( $sortBy, 'desc' );

      $quantity = isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10;
      if ( isset( $filterData['page'] ) ) {
        $offset = $filterData['page'] * $quantity;
        $query->limit( $quantity )->offset( $offset );
      }
      else {
        $query->take( ( isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10 ) );
      }

      $podIds = $query->get();

      $pods = [];
      foreach( $podIds as $podId ) {
        $pod = Pod::where( 'id', $podId->id )->first();
        $pods[] = self::supercharge( $pod, $loggedUserId );
      }
      return $pods;
    }

    static function supercharge( &$pods, $loggedUserId = 0 )
    {
      if ( !is_array( $pods ) ) {
        $pods = [$pods];
      }

      foreach( $pods as &$pod ) {
        $pod->setAttribute( 'owner', $pod->users()->where( 'is_owner', 1 )->first() );
        $pod->setAttribute( 'users', $pod->users()->where( 'is_owner', 0 )->get() );

        $lastPost = $pod->posts()->orderBy( 'created_at', 'desc' )->first();
        if ( $lastPost ) {
          PostRepository::supercharge( $lastPost );
        }
        $pod->setAttribute( 'last_post', $lastPost );
		$postCount = $pod->posts()->count();
        $pod->setAttribute( 'posts_count',  $postCount);
        $pod->setAttribute( 'users_count', $pod->users()->count() );
		
		$is_member = $pod->users()->where( 'user_id', $loggedUserId )
		->where('is_approved', 1)
		->count();
		$pod->setAttribute( 'is_member', $is_member );
		
		if($loggedUserId && $is_member){
			$totalPostRead = Pod::getTotalPostRead($pod, $loggedUserId);
			$totalPostUnread = $postCount - $totalPostRead;
			$pod->setAttribute( 'total_unread', $totalPostUnread );
		}
		else{
			$pod->setAttribute( 'total_unread', 0 );
		}
		
        $pod->setAttribute( 'is_owner', $pod->users()->where( 'user_id', $loggedUserId )->count() );
      }

      if ( is_array( $pods ) && count( $pods ) == 1 ) {
        $pods = $pods[0];
      }

      return $pods;
    }

  }