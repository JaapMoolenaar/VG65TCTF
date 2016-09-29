<?php

namespace DolphinApi\Http\Controllers;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

use Log;

use Illuminate\Http\Request;
use Validator;

use DolphinApi\Http\Requests;
use DolphinApi\Http\Controllers\Controller;
use DolphinApi\Jobs\CommentPushNotification;
use DolphinApi\Jobs\LikePushNotification;

use DolphinApi\Post;
use DolphinApi\User;
use DolphinApi\Link;
use DolphinApi\Like;
use DolphinApi\AbuseReport;
use DolphinApi\Comment;
use DolphinApi\Image;
use DolphinApi\PostType;
use DolphinApi\Topic;
use DolphinApi\Notification;

use DolphinApi\Repositories\PostRepository;
use DolphinApi\Repositories\LikeRepository;
use DolphinApi\Repositories\CommentRepository;
use DolphinApi\Repositories\AbuseReportRepository;

class _PostsController extends ApiGuardController
{
  public function create( Request $request )
  {
    if ( !$this->validate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    $postData = $request->json()->get( 'post' );
    try {
      $postType = PostType::where( "name", $postData['type'] )->first();

      $post = Post::create([
        'title'        => isset( $postData['title'] )  ? $postData['title']  : '',
        'body'         => isset( $postData['body'] )   ? $postData['body']   : '',
        'pod_id'       => isset( $postData['pod_id'] ) ? $postData['pod_id'] : '',
        'user_id'      => $this->apiKey->user_id,
        'post_type_id' => $postType->id
      ]);

      switch( $postData['type'] ) {
        case 'link':
          $link = new Link;
          $link->url = $postData['url'];
          $link->image_url = isset( $postData['image_url'] )    ? $postData['image_url']    : '';
          $link->image_width  = isset( $postData['image_width'] )  ? $postData['image_width']  : 0;
          $link->image_height = isset( $postData['image_height'] ) ? $postData['image_height'] : 0;
          $link->post_id = $post->id;
          $link->save();
        break;

        case 'image':
          $image = new Image;

          if ( isset( $postData['image'] ) ) {
            $image->image_url = $this->updatePostImage( $postData['image'], $post->id );
          }
          else {
            $image->image_url = $postData['image_url'];
          }

          $image->image_width  = isset( $postData['image_width'] )  ? $postData['image_width']  : 0;
          $image->image_height = isset( $postData['image_height'] ) ? $postData['image_height'] : 0;

          $image->post_id = $post->id;
          $image->save();
        break;
      }

      if ( isset( $postData['topics'] ) && $postData['topics'] ) {
        foreach( $postData['topics'] as $topicName ) {
          $topic = Topic::firstOrCreate([
            'name' => $topicName
          ]);
          $post->topics()->attach( $topic );
        }
      }

      PostRepository::supercharge( $post, $this->apiKey->user_id );

      return response([
        "post" => $post,
      ], 200 );

    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to create the post. Please check your input and try again."]
      ], 500 );
    }

  }

  public function filter( Request $request )
  {
    $filterData = $request->json()->get( 'filter' );

    $posts = PostRepository::filter( $filterData, $this->apiKey->user_id );

    return [
      'posts' => $posts
    ];
  }

  public function get( $id )
  {
    $post = Post::find( $id );

    if ( $post ) {
      PostRepository::supercharge( $post, $this->apiKey->user_id );

      return response([
        'post' => $post
      ], 200 );
    }
    else {
      return response([
        "errors" => [ "Post not found." ]
      ], 404 );
    }
  }

  public function getLikes( $id )
  {
    $post = Post::find( $id );

    if ( $post ) {
      $likes = LikeRepository::byPostId( $post->id );

      return response([
        'likes' => $likes
      ], 200 );
    }
    else {
      return response([
        "errors" => [ "Post not found." ]
      ], 404 );
    }
  }

  public function getComments( $id )
  {
    $post = Post::find( $id );

    if ( $post ) {
      $comments = CommentRepository::byPostId( $post->id );

      return response([
        'comments' => $comments
      ], 200 );
    }
    else {
      return response([
        "errors" => [ "Post not found." ]
      ], 404 );
    }
  }

  public function postLike( $id, Request $request )
  {
    $post = Post::find( $id );

    if ( $post ) {
      try {
        $like = Like::create([
          'post_id' => $post->id,
          'user_id' => $this->apiKey->user_id
        ]);

        // Send push notification...
        if ( $post->user->id != $this->apiKey->user_id ) {
          $notification = Notification::create([
            'type' => 0,
            'is_read' => 1,
            'post_id' => $post->id,
            'receiver_id' => $post->user_id,
            'user_id' => $this->apiKey->user_id
          ]);

          dispatch( new LikePushNotification( $like ) );
        }

        LikeRepository::supercharge( $like );

        return [
          'like' => $like
        ];
      }
      catch( \Exception $exception ) {
        return response([
          "errors" => ["An unexpected error occurred trying to create the like. Make sure the user hasn't already liked the post."]
        ], 500 );
      }
    }
    else {
      return response([
        "errors" => [ "Post not found." ]
      ], 404 );
    }
  }

  public function deleteLike( $id, Request $request )
  {
    $post = Post::find( $id );

    if ( $post ) {
      try {
        Like::where( 'post_id', $post->id )
            ->where( 'user_id', $this->apiKey->user_id )
            ->delete();

        return response( "", 204 );
      }
      catch( \Exception $exception ) {
        return response([
          "errors" => ["An unexpected error occurred trying to delete the like. Make sure the user has already liked the post."]
        ], 500 );
      }
    }
    else {
      return response([
        "errors" => [ "Post not found." ]
      ], 404 );
    }
  }

  public function postComment( $id, Request $request )
  {
    $post = Post::find( $id );

    if ( $post ) {
      try {
        $commentData = $request->json()->get( 'comment' );

        $comment = Comment::create([
          'post_id' => $post->id,
          'user_id' => $this->apiKey->user_id,
          'body'    => $commentData['body']
        ]);

        // Send push notification...
        if ( $post->user->id != $this->apiKey->user_id ) {
          $notification = Notification::create([
            'type' => 1,
            'is_read' => 1,
            'post_id' => $post->id,
            'receiver_id' => $post->user_id,
            'user_id' => $this->apiKey->user_id
          ]);
          dispatch( new CommentPushNotification( $comment ) );
        }

        CommentRepository::supercharge( $comment );
        return [
          'comment' => $comment
        ];
      }
      catch( \Exception $exception ) {
        return response([
          "errors" => ["An unexpected error occurred trying to create the comment. Please check your input and try again.", $exception->getMessage()]
        ], 500 );
      }
    }
    else {
      return response([
        "errors" => [ "Post not found." ]
      ], 404 );
    }
  }

  public function postReport( $id )
  {
    $post = Post::find( $id );

    if ( $post ) {
      $reportExists = AbuseReport::where( 'post_id', $post->id )
                                ->where( 'user_id', $this->apiKey->user_id )->count();

      if ( !$reportExists ) {
        try {
          $report = AbuseReport::create([
            'post_id' => $post->id,
            'user_id' => $this->apiKey->user_id
          ]);

          AbuseReportRepository::supercharge( $report );

          return [
            'report' => $report
          ];
        }
        catch( \Exception $exception ) {
          return response([
            "errors" => ["An unexpected error occurred trying to create the report. Please check your input and try again."]
          ], 500 );
        }
      }
      else {
        return response([
          "errors" => ["User already reported post."]
        ], 409 );
      }
    }
    else {
      return response([
        "errors" => [ "Post not found." ]
      ], 404 );
    }
  }

  public function remove( $id )
  {
    Post::destroy( $id );
    return response( "", 204 );
  }

  // PRIVATE ==========================================

  private function validate( $request, &$errors )
  {
    $validation = Validator::make( $request->all(),
      [
        'post.type'    => 'required|in:link,image,text', // TODO: query the post_types table to populate this.
      ],
      [
        'post.type.required' => 'Post Type is required',
        'post.type.in'       => 'Unrecognized Post Type'
      ]);

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    $postData = $request->json()->get( 'post' );
    switch( $postData['type'] ) {
      case 'link':
        if ( !isset( $postData['url'] ) ) {
          $errors[] = "Field 'url' is required for link posts.";
        }
      break;

      case 'image':
        if ( !isset( $postData['image'] ) && !isset( $postData['image_url'] ) ) {
          $errors[] = "Field 'image' or 'image_url' is required for image posts.";
        }
      break;
    }

    return !$errors;
  }

  private function updatePostImage( $imageData, $postId )
  {
    $postImagePath = public_path() . '/img/posts/' . $postId . '.jpg';

    $fpImage = fopen( $postImagePath, 'w+' );
    fwrite( $fpImage, base64_decode( $imageData ) );
    fclose( $fpImage );

    return env( 'ASSETS_URL' ) .  '/img/posts/' . $postId . '.jpg';
  }

}