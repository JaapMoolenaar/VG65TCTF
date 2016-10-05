<?php

namespace DolphinApi\Http\Controllers;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

use Illuminate\Http\Request;
use Validator;

use DolphinApi\Http\Requests;
use DolphinApi\Http\Controllers\Controller;

use DolphinApi\Repositories\TopicRepository;

use DolphinApi\User;
use DolphinApi\Topic;

use DolphinApi\Jobs\Mails\MailTopicCreated;

class TopicsController extends ApiGuardController
{
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
      
      dispatch(new MailTopicCreated( $topic, User::find($this->apiKey->user_id) ));

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
    Topic::destroy( $id );
    return response( "", 204 );
  }

  public function byUser( $userId )
  {
    try {
      $topics = TopicRepository::byUserId( $userId );
      return [
        'topics' => $topics
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

    $topics = TopicRepository::filter( $filterData );

    return [
      'topics' => $topics
    ];
  }

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
}
