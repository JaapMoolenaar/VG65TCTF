<?php

namespace DolphinApi\Jobs;

use Log;

use DolphinApi\Like;
use DolphinApi\User;
use DolphinApi\Post;

use DolphinApi\Jobs\Job;

use Davibennun\LaravelPushNotification\Facades\PushNotification;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class LikePushNotification extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $like;

  /**
   * Create a new job instance.
   *
   * @param Comment $comment
   * @return void
   */
  public function __construct(Like $like)
  {
    $this->like = $like;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $likeUser = User::find( $this->like->user_id );
    $post = Post::find( $this->like->post_id );
    $postOwnerUser  = $post->user;
    $message = PushNotification::Message( $likeUser->username . ' liked on your post: ' . $post->title, [
      'custom' => [
        'post_id' => $post->id
      ]
    ]);
    
//    $token = 'a178fc29a43a40fea102f10d9807c3dc45b46d5c43358e3aa6ea21ea6dd462bb';
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
