<?php

namespace DolphinApi\Jobs\Mails;

use DolphinApi\Topic;
use DolphinApi\User;

use Illuminate\Support\Facades\URL;

use DolphinApi\Jobs\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class MailTopicCreated extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $topic;
  protected $owner;

  /**
   * Create a new job instance.
   *
   * @param  Topic  $topic
   * @param  User $owner
   * @return void
   */
  public function __construct(Topic $topic, User $owner)
  {
    $this->topic = $topic;
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
    $mailer->send( 'emails.topic_created', ['topic' => $this->topic, 'owner' => $this->owner], function ( $message ) {
      $message->to( $this->owner->email, $this->owner->getFullName() )->subject( 'Topic Created' );
    });
  }
}