<?php

  namespace DolphinApi\Repositories;

  use DB;

  use DolphinApi\Comment;

  class CommentRepository
  {

    static function byPostId( $postId )
    {
      $comments = Comment::where( 'post_id', '=', $postId )->get()->all();

      return self::supercharge( $comments );
    }

    static function byUserId( $userId )
    {
      $comments = Comment::where( 'user_id', '=', $userId )->get()->all();

      return self::supercharge( $comments );
    }

    static function supercharge( &$comments )
    {
      if ( !is_array( $comments ) ) {
        $comments = [$comments];
      }

      foreach( $comments as &$comment ) {
        if ( $user = $comment->user()->first() ) {
          $comment->setAttribute( 'user', $user );
        }
        if ( $post = $comment->post()->first() ) {
          $comment->setAttribute( 'post', $post );
        }
      }

      return $comments;
    }

  }