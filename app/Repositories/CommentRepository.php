<?php

  namespace DolphinApi\Repositories;

  use DB;

  use DolphinApi\Comment;

  class CommentRepository
  {

    static function byPostId( $postId, $loggedUserId = 0 )
    {
      $comments = Comment::where( 'post_id', '=', $postId )->get()->all();

      return self::supercharge( $comments, $loggedUserId );
    }

    static function byUserId( $userId, $loggedUserId = 0 )
    {
      $comments = Comment::where( 'user_id', '=', $userId )->get()->all();

      return self::supercharge( $comments, $loggedUserId );
    }

    static function supercharge( &$comments, $loggedUserId = 0 )
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
        
        if ( $image = $comment->image()->first() ) {
          $comment->setAttribute( 'image', $image );
        }
        elseif ( $link  = $comment->link()->first() ) {
          $comment->setAttribute( 'link', $link );
        }
        
        $comment->setAttribute( 'likes_count', $comment->likes()->count() );
        $comment->setAttribute( 'is_liked', $comment->likes()->where( 'user_id', $loggedUserId )->count()  );
      }

      return $comments;
    }

  }