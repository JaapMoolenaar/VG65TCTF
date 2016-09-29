<?php

namespace DolphinApi\Jobs\Mails;

use DolphinApi\Pod;
use DolphinApi\Post;
use DolphinApi\PodUser;
use DolphinApi\User;

use Illuminate\Support\Facades\URL;

use DolphinApi\Jobs\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class MailPostCreated extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $post;
  protected $user;
  protected $owner;

  /**
   * Create a new job instance.
   *
   * @param  Pod  $post
   * @param  User $user
   * @param  User $owner
   * @return void
   */
  public function __construct( Post $post, User $user, User $owner )
  {
    $this->post = $post;
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
    $mailer->send( 'emails.post_created', ['post' => $this->post, 'user' => $this->user, 'owner' => $this->owner], function ( $message ) {
      $message->to( $this->owner->email, $this->owner->getFullName() )->subject( 'Post Created' );
    });
  }
}