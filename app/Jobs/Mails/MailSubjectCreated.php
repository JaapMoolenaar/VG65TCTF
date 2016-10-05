<?php

namespace DolphinApi\Jobs\Mails;

use DolphinApi\Subject;
use DolphinApi\User;

use Illuminate\Support\Facades\URL;

use DolphinApi\Jobs\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class MailSubjectCreated extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $subject;
  protected $owner;

  /**
   * Create a new job instance.
   *
   * @param  Subject  $subject
   * @param  User $owner
   * @return void
   */
  public function __construct(Subject $subject, User $owner)
  {
    $this->subject = $subject;
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
    $mailer->send( 'emails.subject_created', ['subject' => $this->subject, 'owner' => $this->owner], function ( $message ) {
      $message->to( $this->owner->email, $this->owner->getFullName() )->subject( 'Subject Created' );
    });
  }
}