<?php

namespace DolphinApi\Http\Controllers;

use Illuminate\Http\Request;

use DolphinApi\Http\Requests;
use DolphinApi\Http\Controllers\Controller;

use DolphinApi\User;
use Illuminate\Support\Facades\Hash;

use DolphinApi\Repositories\UserRepository;

class TokenController extends Controller
{
    public function byDeviceId( $deviceId )
    {
      $user = User::where( 'device_id', $deviceId )->first();
      if ( $user ) {
        $apiKey = \Chrisbjr\ApiGuard\Models\ApiKey::where( 'user_id', $user->id )->first();
        return response([
          'user' => $user,
          'token' => $apiKey->key
        ]);
      }
      else {
        return response([
          "errors" => [ "Unknown Device ID." ]
        ], 403 );
      }
    }

    public function byUserPass( Request $request )
    {
      $authData = $request->json()->get( 'login' );

      $userOrEmail = $authData['username'];
      $password = $authData['password'];

      $user = User::where( 'username', $userOrEmail  )
                  ->orWhere( 'email', $userOrEmail )
                  ->first();

      if ( $user && Hash::check( $password, $user->password ) ) {
        $apiKey = \Chrisbjr\ApiGuard\Models\ApiKey::where( 'user_id', $user->id )->first();

        // update device token if sent and not present already
        if ( isset( $authData['device_token'] ) && !$user->device_token ) {
          $user->device_token = $authData['device_token'];
          $user->save();
        }

        return response([
          'user' => UserRepository::supercharge( $user, $user->id ),
          'token' => $apiKey->key
        ]);
      }
      else {
        return response([
          "errors" => [ "Invalid Credentials." ]
        ], 403 );
      }
    }
}
