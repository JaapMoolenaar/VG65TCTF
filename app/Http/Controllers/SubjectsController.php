<?php

namespace DolphinApi\Http\Controllers;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

use Illuminate\Http\Request;
use Validator;

use DolphinApi\Http\Requests;
use DolphinApi\Http\Controllers\Controller;

use DolphinApi\Subject;

class SubjectsController extends ApiGuardController
{
  protected $apiMethods = [
    'getAll' => [
      'keyAuthentication' => false
    ],
  ];

  public function getAll()
  {
    return Subject::get();
  }

  public function create( Request $request )
  {
    if ( !$this->validate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    $subjectData = $request->json()->get( 'subject' );
    try {
      $subject = Subject::create([
        'name' => trim( strtolower( $subjectData['name'] ) )
      ]);

      return [
        'subject' => $subject
      ];
    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to create the subject. Please check your input and try again."]
      ], 500 );
    }
  }

  public function remove( $id )
  {
    Subject::destroy( $id );
    return response( "", 204 );
  }

  // PRIVATE ===================================

  private function validate( $request, &$errors )
  {
    $subjectData = $request->all();
    $subjectData['subject']['name'] = trim( strtolower( $subjectData['subject']['name'] ) );

    $validation = Validator::make( $subjectData,
      [ 'subject.name' => 'required|unique:subjects,name'],
      [
        'subject.name.required' => 'Subject name is required',
        'subject.name.unique'   => 'Subject already exists'
      ]
    );

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    return !$errors;
  }

}
