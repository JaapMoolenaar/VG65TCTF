<?php

  namespace DolphinApi\Repositories;

  use DB;

  use DolphinApi\Topic;
  use DolphinApi\Notification;

  class NotificationRepository
  {
    static function filter( $filterData )
    {
      $query = DB::table( 'notifications' );
      $sortBy = 'notifications.created_at';
      $selectFields = ['notifications.notification_id'];
      $query->select( $selectFields );

      if ( isset( $filterData['user_id'] ) && $filterData['user_id'] ) {
        $query->where( 'notifications.receiver_id', "=", $filterData['user_id'] );
      }

      $query->groupBy( 'notifications.notification_id' );
      $query->orderBy( $sortBy, 'desc' );

      $quantity = isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10;
      if ( isset( $filterData['page'] ) ) {
        $offset = $filterData['page'] * $quantity;
        $query->limit( $quantity )->offset( $offset );
      }
      else {
        $query->take( ( isset( $filterData['quantity'] ) ? $filterData['quantity'] : 10 ) );
      }
      $notificationIds = $query->get();

      $notifications = [];
      foreach( $notificationIds as $notificationId ) {
        $notification = Notification::where( 'notification_id', $notificationId->notification_id )->first();
        $notifications[] = self::supercharge( $notification);
      }

      return $notifications;
    }

    static function supercharge( &$notifications)
    {
      if ( !is_array( $notifications ) ) {
        $notifications = [$notifications];
      }

      foreach( $notifications as &$notification ) {
        $notification->setAttribute( 'user', $notification->user()->first() );
        $notification->setAttribute( 'pod', $notification->pod()->first() );
        $notification->setAttribute( 'post', $notification->post()->first() );
      }
      return $notifications;
    }

    static function byUserId( $userId )
    {
      $query = DB::table( 'notifications' );
      $query->select( DB::raw( 'DISTINCT( notification_id )' ), 'name' );

      $query->join( 'posts',        'posts.user_id',  '=', DB::raw( $userId ) );
      $query->join( 'posts_topics', 'posts.id',       '=', 'posts_topics.post_id' );

      $query->groupBy( 'topic_id' );

      $topics = $query->get();

      return $topics;
    }
  }