<?php

  namespace DolphinApi\Repositories;

  use DB;

  use DolphinApi\Like;

  class LikeRepository
  {

    static function byPostId( $postId )
    {
      $likes = Like::where( 'post_id', '=', $postId )->whereNull('comment_id')->get()->all();

      return self::supercharge( $likes );
    }

    static function byUserId( $userId )
    {
      $likes = Like::where( 'user_id', '=', $userId )->whereNull('comment_id')->orderBy( 'created_at', 'desc' )->get()->all();

      return self::supercharge( $likes );
    }

    static function byUserAndPostId( $userId, $postId )
    {
      $likes = Like::where( 'user_id', $userId )->where( 'post_id', $postId )->whereNull('comment_id')->get()->all();

      return self::supercharge( $likes );
    }

    static function supercharge( &$likes )
    {
      if ( !is_array( $likes ) ) {
        $likes = [$likes];
      }

      foreach( $likes as &$like ) {
        if ( $user = $like->user()->first() ) {
          $like->setAttribute( 'user', $user );
        }
        if ( $post = $like->post()->first() ) {
          PostRepository::supercharge( $post, $user->id );
          $like->setAttribute( 'post', $post[0] );
        }
        if ( $comment = $like->comment()->first() ) {
          CommentRepository::supercharge( $comment, $user->id );
          $like->setAttribute( 'comment', $comment[0] );
        }
      }

      return $likes;
    }

  }