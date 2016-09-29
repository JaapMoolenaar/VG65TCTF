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

class MailPodJoinRequestApproval extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $pod;
  protected $user;
  protected $owner;

  /**
   * Create a new job instance.
   *
   * @param  Pod  $pod
   * @param  User $user
   * @param  User $owner
   * @return void
   */
  public function __construct( Pod $pod, User $user, User $owner )
  {
    $this->pod = $pod;
    $this->user = $user;
    $this->owner = $owner;
  }

  /**
   * Execute the job.
   *
   * @param  Mailer  $mailer
   * @return void
   */
  public function handle( Mailer $mailer )
  {
    $mailer->send( 'emails.pod_join_approval', ['pod' => $this->pod, 'user' => $this->user, 'owner' => $this->owner], function ( $message ) {
      $message->to( $this->user->email, $this->user->getFullName() )->subject( 'POD Join Approval' );
    });
  }
}