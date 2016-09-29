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

class PodPushNotification extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $pod;
  protected $podUser;
  protected $type;
  protected $current_user_id;


  /**
   * Create a new job instance.
   *
   * @param Pod $pod
   * @return void
   */
  public function __construct(Pod $pod, PodUser $podUser, $type, $current_user_id)
  {
    $this->pod = $pod;
    $this->podUser = $podUser;
    $this->type = $type;
    $this->current_user_id = $current_user_id;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $message = '';//'Pod Join Test';
    $token = '';//'a178fc29a43a40fea102f10d9807c3dc45b46d5c43358e3aa6ea21ea6dd462bb';

    if($this->type == 0) {
      $podMemberUser = User::find( $this->podUser->user_id);
      $currentUser = User::find( $this->current_user_id);
      $message = PushNotification::Message($currentUser->username . ' added you as a member in pod: ' . $this->pod->name, [
        'custom' => [
           'pod_id' => $this->pod->id
        ]
      ]);

      $token = $podMemberUser->device_token; // TODO: test, remove on production!!!!
    }

    //Join Push notification
    else if($this->type == 1) {
      $ownerPodUser = PodUser::where( 'is_owner', 1 )->where( 'pod_id', $this->pod->id )->first();
      $owner = User::find( $ownerPodUser->user_id);
      $podMemberUser = User::find( $this->podUser->user_id);
      $message = PushNotification::Message($owner->username . ' joined as a member in your pod: ' . $this->pod->name, [
        'custom' => [
           'pod_id' => $this->pod->id
        ]
      ]);

      $token = $owner->device_token; // TODO: test, remove on production!!!!
    }
    
    //Withdraw member Push notification
    else if($this->type == 2) {
      $ownerPodUser = PodUser::where( 'is_owner', 1 )->where( 'pod_id', $this->pod->id )->first();
      $owner = User::find( $ownerPodUser->user_id);
      $podMemberUser = User::find( $this->podUser->user_id);
      $message = PushNotification::Message($owner->username . ' withdrew as a member in your pod: ' . $this->pod->name, [
        'custom' => [
           'pod_id' => $this->pod->id
        ]
      ]);

      $token = $owner->device_token; // TODO: test, remove on production!!!!
    }
    
    try {
        PushNotification::app( 'dolphinIOS' )->to( $token )->send( $message ); // iOS
    }
    catch( \Exception $exception ) {
      Log::error( $exception );
    }
  }
}
