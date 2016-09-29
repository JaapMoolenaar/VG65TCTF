<?php

  namespace DolphinApi\Repositories;

  use DB;

  use DolphinApi\User;

  class UserRepository
  {
    static function filter( $filterData, $loggedUserId = 0 )
    {
      $query = DB::table( 'users' );
      $query->select( 'users.id' );

      if ( isset( $filterData['pattern'] ) && $filterData['pattern'] ) {
        $pattern = strtolower( $filterData['pattern'] );
        $query->whereRaw(
          '( LOWER( users.username )   LIKE ? OR
             LOWER( users.first_name ) LIKE ? OR
             LOWER( users.last_name )  LIKE ? )',
          ["%$pattern%", "%$pattern%", "%$pattern%"]
        ); // TODO: fulltext search?
      }

      if ( isset( $filterData['pod_id'] ) && $filterData['pod_id'] ) {
        $query->join( 'pods_users', 'users.id', '=', 'pods_users.user_id' );
        $query->join( 'pods', 'pods.id', '=', 'pods_users.pod_id' );
        $query->where( 'pods.id', $filterData['pod_id'] );
      }

      if ( isset( $filterData['from_date'] ) && $filterData['from_date'] ) {
        $query->where( 'users.created_at', ">=", $filterData['from_date'] );
      }

      if ( isset( $filterData['to_date'] ) && $filterData['to_date'] ) {
        $query->where( 'users.created_at', "<=", $filterData['to_date'] );
      }

      $query->groupBy( 'users.id' );
      $query->orderBy( 'users.updated_at', 'desc' );

      $quantity = isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10;
      if ( isset( $filterData['page'] ) ) {
        $offset = $filterData['page'] * $quantity;
        $query->limit( $quantity )->offset( $offset );
      }
      else {
        $query->take( ( isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10 ) );
      }

      $userIds = $query->get();

      $users = [];
      foreach( $userIds as $userId ) {
        $user    = User::where( 'id', $userId->id )->first();
        $users[] = self::supercharge( $user, $loggedUserId );
      }

      return $users;
    }

    static function supercharge( &$users, $loggedUserId = 0 )
    {
      if ( !is_array( $users ) ) {
        $users = [$users];
      }

      foreach( $users as &$user ) {
        //$user->setAttribute( 'roles',    $user->roles()->get() );
        $user->setAttribute( 'grades',   $user->grades()->get() );
        $user->setAttribute( 'subjects', $user->subjects()->get() );
        $user->setAttribute( 'pods',     $user->pods()->where( 'is_owner', 1 )->get() );
      }

      if ( is_array( $users ) && count( $users ) == 1 ) {
        $users = $users[0];
      }

      return $users;
    }
  }