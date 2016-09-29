<?php

namespace DolphinApi\Jobs;

use Log;

use DolphinApi\PodUser;
use DolphinApi\User;
use DolphinApi\Pod;

use DolphinApi\Jobs\Job;

use Davibennun\LaravelPushNotification\Facades\PushNotification;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class PodMemberLeavePushNotification extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $podId;
  protected $leaveUserId;
  protected $podOwnerUserId;
  /**
   * Create a new job instance.
   *
   * @param Pod $pod
   * @return void
   */
  public function __construct($podId, $leaveUserId, $podOwnerUserId)
  {
    $this->podId = $podId;
    $this->leaveUserId = $leaveUserId;
    $this->podOwnerUserId = $podOwnerUserId;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $podOwnerUser = User::find( $this->podOwnerUserId );
    $leaveUser = User::find( $this->leaveUserId);
    $pod = Pod::find( $this->podId );

    $message = PushNotification::Message( $leaveUser->username . ' withdraw member from your pod: ' . $pod->name, [
      'custom' => [
        'pod_id' => $pod->id
      ]
    ]);

    $token = $podOwnerUser->device_token; // TODO: test, remove on production!!!!
    if(isset($token) && $token != '') {
      try {
        PushNotification::app( 'dolphinIOS' )->to( $token )->send( $message ); // iOS
      } 
      catch( \Exception $exception ) {
        Log::error( $exception );
      }
    }

  }
}
