<?php

namespace DolphinApi\Jobs\Mails;

use DolphinApi\Pod;
use DolphinApi\Post;
use DolphinApi\Comment;
use DolphinApi\PodUser;
use DolphinApi\User;

use Illuminate\Support\Facades\URL;

use DolphinApi\Jobs\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class MailCommentLiked extends Job implements ShouldQueue, SelfHandling
{
  use InteractsWithQueue, SerializesModels;

  protected $post;
  protected $comment;
  protected $user;
  protected $owner;

  /**
   * Create a new job instance.
   * $user liked $owner's post
   * 
   * @param  Pod  $post
   * @param  User $user
   * @param  User $owner
   * @return void
   */
  public function __construct( Post $post, Comment $comment, User $user, User $owner )
  {
    $this->post = $post;
    $this->comment = $comment;
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
    $mailer->send( 'emails.comment_liked', ['post' => $this->post, 'comment' => $this->comment, 'user' => $this->user, 'owner' => $this->owner], function ( $message ) {
      $message->to( $this->owner->email, $this->owner->getFullName() )->subject( 'Comment Liked' );
    });
  }
}