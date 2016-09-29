<?php

namespace DolphinApi\Jobs;

use DolphinApi\Pod;
use DolphinApi\PodUser;
use DolphinApi\User;

use Illuminate\Support\Facades\URL;

use DolphinApi\Jobs\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class MailPodJoinRequest extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $user;
  protected $pod;

  /**
   * Create a new job instance.
   *
   * @param  User $user
   * @param  Pod  $pod
   * @return void
   */
  public function __construct( Pod $pod, User $user )
  {
    $this->pod  = $pod;
    $this->user = $user;
  }

  /**
   * Execute the job.
   *
   * @param  Mailer  $mailer
   * @return void
   */
  public function handle( Mailer $mailer )
  {
    $ownerPodUser = PodUser::where( 'pod_id', $this->pod->id )->where( 'is_owner', true )->first();
    if ( $ownerPodUser ) {
      $ownerUser = User::find( $ownerPodUser->user_id );

      $mailer->send( 'emails.pod_join_request', ['pod' => $this->pod, 'user' => $this->user, 'owner' => $ownerUser], function ( $message ) use ( $ownerUser ) {
        $message->to( $ownerUser->email, "$ownerUser->first_name $ownerUser->last_name" )->subject( 'POD Join Request' );
      });
    }
  }
}