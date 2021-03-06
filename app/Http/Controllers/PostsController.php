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

use DolphinApi\Jobs\Mails\MailPostCreated;
use DolphinApi\Jobs\Mails\MailPostLiked;
use DolphinApi\Jobs\Mails\MailPostCommentCreated;
use DolphinApi\Jobs\Mails\MailCommentLiked;

use DolphinApi\Post;
use DolphinApi\PodUser;
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

class PostsController extends ApiGuardController
{
  public function create( Request $request )
  {
    if ( !$this->validate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    try {
      $postData = $request->json()->get( 'post' );
    
      $postType = PostType::where( "name", $postData['type'] )->first();
      $userId = $this->apiKey->user_id;
      $podId = $postData['pod_id'];
     
      $post = Post::create([
        'title'        => isset( $postData['title'] )  ? $postData['title']  : '',
        'body'         => isset( $postData['body'] )   ? $postData['body']   : '',
        'pod_id'       => $podId,
        'user_id'      => $userId,
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

      $podOwner = PodUser::where( 'is_owner', 1 )
        ->where( 'pod_id', $podId )
        ->first();

      if ($podOwner->user_id != $userId) {
        dispatch(new MailPostCreated( $post, User::find($userId), $podOwner->user ));
      }

      PostRepository::supercharge( $post, $userId );
      
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

  public function update( Request $request )
  {
    if ( !$this->validate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    try {
      $postData = $request->json()->get( 'post' );
      $userId = $this->apiKey->user_id;
      $postId = $postData['id'];

      $post = Post::findOrFail( $postId );
      
      $oldPostTypeId = $post->post_type_id;
      $oldPostType = PostType::find( $oldPostTypeId );
      
      if (isset($postData['type'])) {
        $postType = PostType::where( "name", $postData['type'] )->first();
        $postTypeId = $postType->id;
      } else {
        $postType = $oldPostType;
        $postTypeId = $oldPostTypeId;
      }
      
      // The post type changed!
      // We'll delete old links and images
      if ($postTypeId != $oldPostTypeId) {
        switch( $oldPostType->name ) {
          case 'link':
            Link::where('post_id', $postId)->delete();
            break;
          case 'image':
            Image::where('post_id', $postId)->delete();
            $this->deletePostImage($postId);
            break;
        }
      }
      
      // Now it's time to update the link or image
      switch( $postType->name ) {
        case 'link':
          $link = Link::where('post_id', $postId)->first();
          if (!$link) {
            $link = new Link;
          }
          
          $link->url = $postData['url'];
          $link->image_url = isset( $postData['image_url'] )    ? $postData['image_url']    : '';
          $link->image_width  = isset( $postData['image_width'] )  ? $postData['image_width']  : 0;
          $link->image_height = isset( $postData['image_height'] ) ? $postData['image_height'] : 0;
          $link->post_id = $post->id;
          $link->save();
        break;

        case 'image':
          $image = Image::where('post_id', $postId)->first();
          if (!$image) {
            $image = new Image;
          }

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
      
      // Then update otherfields:
      $update = array_intersect_key($postData, array_flip(['title', 'post', 'pod_id']));
      $update['post_type_id'] = $postType->id;
      
      $post->update($update);
      
      // Update the topics, if needed
      if ( isset( $postData['topics'] ) && $postData['topics'] ) {
        // Detach all first
        $post->topics()->detach();
        
        // Then reattach new ones
        foreach( $postData['topics'] as $topicName ) {
          $topic = Topic::firstOrCreate([
            'name' => $topicName
          ]);
          $post->topics()->attach( $topic );
        }
      }
      
      /*
      $podOwner = PodUser::where( 'is_owner', 1 )
        ->where( 'pod_id', $podId )
        ->first();

      if ($podOwner->user_id != $userId) {
        dispatch(new MailPostCreated( $post, User::find($userId), $podOwner->user ));
      }
      */
      
      PostRepository::supercharge( $post, $userId );
      
      return response([
        "post" => $post,
      ], 200 );

    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to update the post. Please check your input and try again."],
        "exceptions" => exception_array_all($exception)
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
      $comments = CommentRepository::byPostId( $post->id, $this->apiKey->user_id );

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
        $userId = $this->apiKey->user_id;
                
        $like = Like::create([
          'post_id' => $post->id,
          'user_id' => $userId
        ]);

        // Send push notification...
        if ( $post->user->id != $userId ) {
          $notification = Notification::create([
            'type' => 0,
            'is_read' => 1,
            'post_id' => $post->id,
            'receiver_id' => $post->user_id,
            'user_id' => $userId
          ]);

          dispatch( new LikePushNotification( $like ) );
          
          dispatch( new MailPostLiked( $post, User::find($userId), $post->user ));
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
    if ( !$this->validateComment( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }
    
    $post = Post::find( $id );

    if ( $post ) {
      try {
        $userId = $this->apiKey->user_id;
        $commentData = $request->json()->get( 'comment' );
        $commentType = $commentData['type'];
        
        $comment = Comment::create([
          'post_id' => $post->id,
          'user_id' => $userId,
          'body'    => $commentData['body'],
          'type'    => $commentType
        ]);

        switch( $commentType ) {
          case 'link':
            $link = new Link;
            $link->url = $commentData['url'];
            $link->image_url    = isset( $commentData['image_url'] )    ? $commentData['image_url']    : '';
            $link->image_width  = isset( $commentData['image_width'] )  ? $commentData['image_width']  : 0;
            $link->image_height = isset( $commentData['image_height'] ) ? $commentData['image_height'] : 0;
            $link->post_id = $post->id;
            $link->comment_id = $comment->id;
            $link->save();
          break;

          case 'image':
            $image = new Image;

            if ( isset( $commentData['image'] ) ) {
              $image->image_url = $this->updateCommentImage( $commentData['image'], $post->id );
            }
            else {
              $image->image_url = $commentData['image_url'];
            }

            $image->image_width  = isset( $commentData['image_width'] )  ? $commentData['image_width']  : 0;
            $image->image_height = isset( $commentData['image_height'] ) ? $commentData['image_height'] : 0;

            $image->post_id = $post->id;
            $image->comment_id = $comment->id;
            $image->save();
          break;
        }

        // Send push notification...
        if ( $post->user->id != $userId ) {
          $notification = Notification::create([
            'type' => 1,
            'is_read' => 1,
            'post_id' => $post->id,
            'receiver_id' => $post->user_id,
            'user_id' => $userId
          ]);
          dispatch( new CommentPushNotification( $comment ) );
          
          dispatch( new MailPostCommentCreated( $post, $comment, User::find($userId), $post->user ));
        }

        CommentRepository::supercharge( $comment, $this->apiKey->user_id );
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


  public function postCommentLike( $id, $commentId, Request $request )
  {
    $post = Post::findOrFail( $id );
    $comment = Comment::findOrFail( $commentId );

    if ($post 
        && $comment 
        && $post->id == $comment->post_id 
    ) {
      try {
        $userId = $this->apiKey->user_id;
                
        $like = Like::create([
          'post_id' => $post->id,
          'comment_id' => $comment->id,
          'user_id' => $userId
        ]);

        // Send push notification...
        if ( $post->user->id != $userId ) {
          $notification = Notification::create([
            'type' => 0,
            'is_read' => 1,
            'post_id' => $post->id,
            'comment_id' => $comment->id,
            'receiver_id' => $post->user_id,
            'user_id' => $userId
          ]);

          dispatch( new LikePushNotification( $like ) );
          
          dispatch( new MailCommentLiked( $post, $comment, User::find($userId), $post->user ));
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

  public function deleteCommentLike( $id, $commentId, Request $request )
  {
    $post = Post::find( $id );
    $comment = Comment::findOrFail( $commentId );

    if ($post 
        && $comment 
        && $post->id == $comment->post_id 
    ) {
      try {
        Like::where( 'post_id', $post->id )
            ->where( 'comment_id', $comment->id )
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
        'post.pod_id'  => 'required|numeric', // TODO: query the post_types table to populate this.
      ],
      [
        'post.type.required'    => 'Post Type is required',
        'post.type.in'          => 'Unrecognized Post Type',
        'post.pod_id.required'  => 'Post Type is required',
        'post.pod_id.numeric'   => 'Invalid pod id value',
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

  private function validateComment( $request, &$errors )
  {
    $validation = Validator::make( $request->all(),
      [
        'comment.type'    => 'required|in:image,text', // ,link
        'comment.body'    => 'required',
      ],
      [
        'comment.type.required'    => 'Cpmment Type is required',
        'comment.type.in'          => 'Unrecognized Comment Type',
        'comment.body.required'    => 'Comment Body is required',
      ]);

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    $commentData = $request->json()->get( 'comment' );
    switch( $commentData['type'] ) {
      case 'link':
        if ( !isset( $commentData['url'] ) ) {
          $errors[] = "Field 'url' is required for link comments.";
        }
      break;

      case 'image':
        if ( !isset( $commentData['image'] ) && !isset( $commentData['image_url'] ) ) {
          $errors[] = "Field 'image' or 'image_url' is required for image comments.";
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
  
  private function deletePostImage( $postId )
  {
    $postImagePath = public_path() . '/img/posts/' . $postId . '.jpg';

    if (file_exists($postImagePath)) {
      unlink($postImagePath);
    }
  }
  
  private function updateCommentImage( $imageData, $commentId )
  {
    $commentImagePath = public_path() . '/img/comments/' . $commentId . '.jpg';

    $fpImage = fopen( $commentImagePath, 'w+' );
    fwrite( $fpImage, base64_decode( $imageData ) );
    fclose( $fpImage );

    return env( 'ASSETS_URL' ) .  '/img/comments/' . $commentId . '.jpg';
  }
  
  private function deleteCommentImage( $commentId )
  {
    $commentImagePath = public_path() . '/img/comments/' . $commentId . '.jpg';

    if (file_exists($commentImagePath)) {
      unlink($commentImagePath);
    }
  }

}