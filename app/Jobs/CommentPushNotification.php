<?php

namespace DolphinApi\Jobs;

use Log;

use DolphinApi\Comment;
use DolphinApi\User;
use DolphinApi\Post;

use DolphinApi\Jobs\Job;

use Davibennun\LaravelPushNotification\Facades\PushNotification;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentPushNotification extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $comment;

  /**
   * Create a new job instance.
   *
   * @param Comment $comment
   * @return void
   */
  public function __construct(Comment $comment)
  {
    $this->comment = $comment;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $commentingUser = User::find( $this->comment->user_id );

    $post = Post::find( $this->comment->post_id );
    $postOwnerUser  = $post->user;

    $message = PushNotification::Message( $commentingUser->username . ' commented on your post: ' . $this->comment->body, [
      'custom' => [
        'post_id' => $post->id
      ]
    ]);

    $token = $postOwnerUser->device_token; // TODO: test, remove on production!!!!

    try {
        PushNotification::app( 'dolphinIOS' )->to( $token )->send( $message ); // iOS
//      PushNotification::app( 'dolphinAndroid' )->to( $devices )->send( $message ); // Android
    }
    catch( \Exception $exception ) {
      Log::error( $exception );
    }
  }
}
