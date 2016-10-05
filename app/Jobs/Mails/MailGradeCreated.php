<?php

namespace DolphinApi\Jobs\Mails;

use DolphinApi\Grade;
use DolphinApi\User;

use Illuminate\Support\Facades\URL;

use DolphinApi\Jobs\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class MailGradeCreated extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $grade;
  protected $owner;

  /**
   * Create a new job instance.
   *
   * @param  Grade  $grade
   * @param  User $owner
   * @return void
   */
  public function __construct(Grade $grade, User $owner)
  {
    $this->grade = $grade;
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
    $mailer->send( 'emails.grade_created', ['grade' => $this->grade, 'owner' => $this->owner], function ( $message ) {
      $message->to( $this->owner->email, $this->owner->getFullName() )->subject( 'Grade Created' );
    });
  }
}