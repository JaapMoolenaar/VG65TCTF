<?php

namespace DolphinApi\Http\Controllers;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

use Illuminate\Http\Request;
use Validator;

use DolphinApi\Http\Requests;
use DolphinApi\Http\Controllers\Controller;

use DolphinApi\User;
use DolphinApi\Grade;

use DolphinApi\Jobs\Mails\MailGradeCreated;

class GradesController extends ApiGuardController
{
  protected $apiMethods = [
    'getAll' => [
      'keyAuthentication' => false
    ],
  ];

  public function getAll()
  {
    return Grade::get();
  }

  public function create( Request $request )
  {
    if ( !$this->validate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    $gradeData = $request->json()->get( 'grade' );
    try {
      $grade = Grade::create([
        'name' => trim( strtolower( $gradeData['name'] ) )
      ]);
      
      dispatch(new MailGradeCreated( $grade, User::find($this->apiKey->user_id) ));

      return [
        'grade' => $grade
      ];
    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to create the grade. Please check your input and try again."]
      ], 500 );
    }
  }

  public function remove( $id )
  {
    Grade::destroy( $id );
    return response( "", 204 );
  }

  // PRIVATE ===================================

  private function validate( $request, &$errors )
  {
    $gradeData = $request->all();
    $gradeData['grade']['name'] = trim( strtolower( $gradeData['grade']['name'] ) );

    $validation = Validator::make( $gradeData,
      [ 'grade.name' => 'required|unique:grades,name'],
      [
        'grade.name.required' => 'Grade name is required',
        'grade.name.unique'   => 'Grade already exists'
      ]
    );

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    return !$errors;
  }

}