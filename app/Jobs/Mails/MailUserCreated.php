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

class MailUserCreated extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $user;
  protected $password;

  /**
   * Create a new job instance.
   *
   * @param  User $user
   * @param  string $password
   * @return void
   */
  public function __construct( User $user, $password )
  {
    $this->user = $user;
    $this->password = $password;
  }

  /**
   * Execute the job.
   *
   * @param  Mailer  $mailer
   * @return void
   */
  public function handle( Mailer $mailer )
  {
    $mailer->send( 'emails.user_created', ['user' => $this->user, 'password' => $this->password], function ( $message ) {
      $message->to( $this->user->email, $this->user->getFullName() )->subject( 'Welcome' );
    });
  }
}