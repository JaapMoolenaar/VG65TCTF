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

class MailPodCreated extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $pod;
  protected $owner;

  /**
   * Create a new job instance.
   *
   * @param  Pod  $pod
   * @param  User $owner
   * @return void
   */
  public function __construct( Pod $pod, User $owner)
  {
    $this->pod  = $pod;
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
    $mailer->send( 'emails.pod_created', ['pod' => $this->pod, 'owner' => $this->owner], function ( $message ) {
      $message->to( $this->owner->email, $this->owner->getFullName() )->subject( 'POD Created' );
    });
  }
}