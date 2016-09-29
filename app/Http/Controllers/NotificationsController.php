<?php

namespace DolphinApi\Http\Controllers;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

use Illuminate\Http\Request;
use Validator;

use DolphinApi\Http\Requests;
use DolphinApi\Http\Controllers\Controller;

use DolphinApi\Repositories\NotificationRepository;

use DolphinApi\Notification;

class NotificationsController extends ApiGuardController
{
  /*
  public function create( Request $request )
  {
    if ( !$this->validate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    $topicData = $request->json()->get( 'topic' );
    try {
      $topic = Topic::create([
        'name' => trim( strtolower( $topicData['name'] ) )
      ]);

      return [
        'topic' => $topic
      ];
    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to create the topic. Please check your input and try again."]
      ], 500 );
    }
  }

  public function remove( $id )
  {
    Notification::destroy( $id );
    return response( "", 204 );
  }
  */
  public function byUser( $userId )
  {
    try {
      $notifications = NotificationRepository::byUserId( $userId );
      return [
        'notifications' => $notifications
      ];
    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to get the topics. Please check your request and try again."]
      ], 500 );
    }
  }

  public function filter( Request $request )
  {
    $filterData = $request->json()->get( 'filter' );
    $notifications = NotificationRepository::filter( $filterData );

    return [
      'notifications' => $notifications
    ];
  }
/*
  // PRIVATE ===================================

  private function validate( $request, &$errors )
  {
    $topicData = $request->all();
    $topicData['topic']['name'] = trim( strtolower( $topicData['topic']['name'] ) );

    $validation = Validator::make( $topicData,
      [ 'topic.name' => 'required|unique:topics,name'],
      [
        'topic.name.required' => 'Topic name is required',
        'topic.name.unique'   => 'Topic already exists'
      ]
    );

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    return !$errors;
  }
  */
}
