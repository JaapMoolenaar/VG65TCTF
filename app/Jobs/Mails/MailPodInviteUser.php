<?php

namespace DolphinApi\Jobs\Mails;

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

class MailPodInviteUser extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $user;
  protected $pod;
  protected $podUser;
  protected $ownerId;

  /**
   * Create a new job instance.
   *
   * @param  Pod  $pod
   * @param  PodUser $podUser
   * @param  integer $ownerId
   * @return void
   */
  public function __construct( Pod $pod, PodUser $podUser , $ownerId)
  {
    $this->pod  = $pod;
    $this->podUser = $podUser;
	$this->user = User::find($podUser->user_id);
	$this->ownerId = $ownerId;
  }

  /**
   * Execute the job.
   *
   * @param  Mailer  $mailer
   * @return void
   */
  public function handle( Mailer $mailer )
  {
	$ownerUser = User::find( $this->ownerId );
    if ( $ownerUser ) {
      $mailer->send( 'emails.pod_invite_request', ['pod' => $this->pod, 'user' => $this->user, 'owner' => $ownerUser, 'podUser' => $this->podUser], function ( $message ) use ( $ownerUser ) {
        $message->to( $this->user->email, $ownerUser->getFullName() )->subject( 'POD Invite Request' );
      });
    }
  }
}