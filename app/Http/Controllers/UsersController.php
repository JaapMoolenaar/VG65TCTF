<?php

namespace DolphinApi\Http\Controllers;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

use Illuminate\Http\Request;
use Validator;

use DolphinApi\Http\Requests;
use DolphinApi\Http\Controllers\Controller;

use DolphinApi\User;
use DolphinApi\Like;
use DolphinApi\Grade;
use DolphinApi\Subject;
use DolphinApi\Repositories\LikeRepository;
use DolphinApi\Repositories\CommentRepository;
use DolphinApi\Repositories\UserRepository;

use Illuminate\Support\Facades\Hash;

class UsersController extends ApiGuardController
{

  protected $apiMethods = [
    'create' => [
      'keyAuthentication' => false
    ],
  ];

  // [POST] /api/v1/users
  public function create( Request $request )
  {
    if ( !$this->validate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    $userData = $request->json()->get( 'user' );
    try {
      $user = User::create([
        'device_id'    => isset( $userData['device_id'] )    ? $userData['device_id']              : '',
        'device_token' => isset( $userData['device_token'] ) ? $userData['device_token']           : '',
        'email'        => isset( $userData['email'] )        ? $userData['email']                  : uniqid() . "@noemail.com",
        'password'     => isset( $userData['password'] )     ? Hash::make( $userData['password'] ) : Hash::make( uniqid() ),
        "username"     => isset( $userData['username'] )     ? $userData['username']               : uniqid(),
        "first_name"   => isset( $userData['first_name'] )   ? $userData['first_name']             : '',
        "last_name"    => isset( $userData['last_name'] )    ? $userData['last_name']              : '',
        "location"     => isset( $userData['location'] )     ? $userData['location']               : '',
        "city"         => isset( $userData['city'] )         ? $userData['city']                   : '',
        "country"      => isset( $userData['country'] )      ? $userData['country']                : '',
        "zip"          => isset( $userData['zip'] )          ? $userData['zip']                    : '',        
        "gender"       => isset( $userData['gender'] )       ? $userData['gender']                 : 0,
        "is_private"   => isset( $userData['is_private'] )   ? $userData['is_private']             : 0
      ]);

      $apiKey = new \Chrisbjr\ApiGuard\Models\ApiKey;
      $apiKey->key = $apiKey->generateKey();
      $apiKey->user_id = $user->id;
      $apiKey->save();

      if ( isset( $userData['avatar_image'] ) && $userData['avatar_image'] ) {
        $user->avatar_image_url = $this->updateAvatarImage( $userData['avatar_image'], $user->id );
        $user->save();
      }

      if ( isset( $userData['grades'] ) && $userData['grades'] ) {
        foreach( $userData['grades'] as $gradeIdentifier ) {
          if ( is_int( $gradeIdentifier ) ) { // it's an id
            $grade = Grade::find( $gradeIdentifier );
          }
          else {
            $grade = Grade::where( 'name', $gradeIdentifier )->first();
          }

          if ( $grade ) {
            $user->grades()->attach( $grade );
          }
        }
      }

      if ( isset( $userData['subjects'] ) && $userData['subjects'] ) {
        foreach( $userData['subjects'] as $subjectIdentifier ) {
          if ( is_int( $subjectIdentifier ) ) { // it's an id
            $subject = Subject::find( $subjectIdentifier );
          }
          else {
            $subject = Subject::where( 'name', $subjectIdentifier )->first();
          }
          if ( $subject ) {
            $user->subjects()->attach( $subject );
          }
        }
      }

      UserRepository::supercharge( $user, $apiKey->key );

      return response([
        "user"      => $user,
        "api_token" => $apiKey->key
      ], 200 );

    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to register the user. Please check your input and try again." ]
      ], 500 );
    }
  }

  // [GET] /api/v1/users/{id}
  public function get( $id )
  {
    $user = User::find( $id );

    if ( $user ) {
      UserRepository::supercharge( $user, $this->apiKey->user_id );

      return response([
        "user" => $user
      ], 200 );
    }
    else {
      return response([
        "errors" => [ "User not found." ]
      ], 404 );
    }
  }

  // [PATCH] /api/v1/users
  // Updates the authenticated user data
  public function update( Request $request )
  {
    if ( !$this->validateUpdate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    $user = User::find( $this->apiKey->user_id );

    try {
      $userData = $request->json()->get( 'user' );

      foreach( $userData as $fieldName => $fieldValue ) {
        switch ( $fieldName ) {
          case 'avatar_image':
            $user->avatar_image_url = $this->updateAvatarImage( $userData['avatar_image'], $user->id );
          break;

          case 'password':
            $user->$fieldName = Hash::make( $fieldValue );
          break;

          case 'grades':
            $user->grades()->detach();
            foreach( $userData['grades'] as $gradeIdentifier ) {
              $isId = (int)$gradeIdentifier ? true : false;
              if ( $isId ) {
                $grade = Grade::find( $gradeIdentifier );
              }
              else {
                $grade = Grade::where( 'name', $gradeIdentifier )->first();
              }

              if ( $grade ) {
                $user->grades()->attach( $grade );
              }
            }
          break;

          case 'subjects':
            $user->subjects()->detach();
            foreach( $userData['subjects'] as $subjectIdentifier ) {
              $isId = (int)$subjectIdentifier ? true : false;
              if ( $isId ) {
                $subject = Subject::find( $subjectIdentifier );
              }
              else {
                $subject = Subject::where( 'name', $subjectIdentifier )->first();
              }
              if ( $subject ) {
                $user->subjects()->attach( $subject );
              }
            }
          break;

          default:
            $user->$fieldName = $fieldValue;
          break;
        }
      }

      $user->save();
    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to update the user. Please check your input and try again."]
      ], 500 );
    }

    UserRepository::supercharge( $user, $this->apiKey->user_id );

    return response([
      "user" => $user
    ], 200 );
  }

  public function getLikes( $id )
  {
    $user = User::find( $id );

    if ( $user ) {
      $likes = LikeRepository::byUserId( $user->id );

      return response([
        'likes' => $likes
      ], 200 );
    }
    else {
      return response([
        "errors" => [ "User not found." ]
      ], 404 );
    }
  }

  public function getLikesByPost( $userId, $postId )
  {
    $user = User::find( $userId );

    if ( $user ) {
      $likes = LikeRepository::byUserAndPostId( $userId, $postId );

      if ( $likes ) {
        return response([
          'likes' => $likes
        ], 200 );
      }
      else {
        return response([
          "errors" => [ "Like not found." ]
        ], 404 );
      }
    }
    else {
      return response([
        "errors" => [ "User not found." ]
      ], 404 );
    }
  }

  public function getComments( $id )
  {
    $user = User::find( $id );

    if ( $user ) {
      $comments = CommentRepository::byUserId( $user->id );

      return response([
        'comments' => $comments
      ], 200 );
    }
    else {
      return response([
        "errors" => [ "User not found." ]
      ], 404 );
    }
  }

  public function filter( Request $request )
  {
    $filterData = $request->json()->get( 'filter' );

    $users = UserRepository::filter( $filterData, $this->apiKey->user_id );

    return [
      'users' => $users
    ];
  }

  // PRIVATE ==============================================================

  private function updateAvatarImage( $imageData, $userId )
  {
    $avatarImagePath = public_path() . '/img/avatars/' . $userId . '.jpg';

    $fpImage = fopen( $avatarImagePath, 'w+' );
    fwrite( $fpImage, base64_decode( $imageData ) );
    fclose( $fpImage );

    return env( 'ASSETS_URL' ) . '/img/avatars/' . $userId . '.jpg';
  }

  protected function validate( $request, &$errors )
  {
    $validation = Validator::make( $request->all(), [
//      'user.device_id' => 'required|string|unique:users,device_id',
      'user.email'     => 'email|unique:users,email',
      'user.username'  => 'string|unique:users,username'
    ],
    [
//      'user.device_id.unique'   => 'Device already registered.',
//      'user.device_id.required' => 'Device ID is required.',
      'user.email.email'        => 'Please provide a valid email address',
      'user.username.unique'    => 'Username already registered'
    ]);

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    return !$errors;
  }

  protected function validateUpdate( $request, &$errors )
  {
    $validation = Validator::make( $request->all(), [
//      'user.device_id' => 'string|unique:users,device_id',
      'user.email'     => 'email|unique:users,email',
      'user.username'  => 'string|unique:users,username'
    ],
    [
//      'user.device_id.unique' => 'Device already registered',
      'user.email.email'      => 'Please provide a valid email address',
      'user.email.unique'     => 'Email already registered',
      'user.username.unique'  => 'Username already registered'
    ]);

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    return !$errors;
  }

}