<?php

namespace DolphinApi\Http\Controllers;

use Illuminate\Support\Facades\View;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

use Illuminate\Http\Request;
use Validator;

use DolphinApi\Http\Requests;

use DolphinApi\Pod;
use DolphinApi\User;
use DolphinApi\PodUser;
use DolphinApi\Notification;

use DolphinApi\Repositories\PodRepository;
use DolphinApi\Jobs\PodPushNotification;
use DolphinApi\Jobs\PodMemberLeavePushNotification;

use DolphinApi\Jobs\Mails\MailPodCreated;
use DolphinApi\Jobs\Mails\MailPodJoinRequest;
use DolphinApi\Jobs\Mails\MailPodJoinRequestApproval;
use DolphinApi\Jobs\Mails\MailPodInviteUser;
use DolphinApi\Jobs\Mails\MailPodInviteUserAcceptance;
use DolphinApi\Jobs\Mails\MailPodUserRemoval;

class PodsController extends ApiGuardController
{

  protected $apiMethods = [
    'userApprovalLink' => [
      'keyAuthentication' => false
    ],
	'userAcceptInviteRequest' => [
		'keyAuthentication' => false
	]
  ];

  public function create( Request $request )
  {
    if ( !$this->validate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    $podData = $request->json()->get( 'pod' );
    try {
      $pod = Pod::create([
        'name'           => $podData['name'],
        'description'    => isset( $podData['description'] )  ? $podData['description']  : '',
        'is_private'     => isset( $podData['is_private'] )   ? $podData['is_private']   : 0,
        'image_width'    => isset( $podData['image_width'] )  ? $podData['image_width']  : 0,
        'image_height'   => isset( $podData['image_height'] ) ? $podData['image_height'] : 0,
        'approval_token' => uniqid()
      ]);

      if ( $pod ) {
        $userIds   = isset( $podData['users'] ) ? $podData['users'] : [];
        if ( !in_array( $this->apiKey->user_id, $userIds ) ) {
          $userIds[] = $this->apiKey->user_id;
        }

        foreach( $userIds as $userId ) {
          $podUser = PodUser::create([
            'user_id'     => $userId,
            'pod_id'      => $pod->id,
            'is_owner'    => $userId == $this->apiKey->user_id,
            'is_approved' => $userId == $this->apiKey->user_id,
			'invite_token' => uniqid()
          ]);

          if ( $userId != $this->apiKey->user_id ) {
            // Send push notification...
            $notification = Notification::create([
              'type' => 4,
              'is_read' => 1,
              'pod_id' => $pod->id,
              'receiver_id' => $userId,
              'user_id' => $this->apiKey->user_id,
              ]);
            
			// todo: send email to user to invite request
			dispatch( new MailPodInviteUser( $pod, $podUser, $this->apiKey->user_id));
			
            dispatch( new PodPushNotification($pod, $podUser, 0, $this->apiKey->user_id));
          }
        }

        if ( isset( $podData['image'] ) ) {
          $pod->image_url = $this->updatePodImage( $podData['image'], $pod->id );
          $pod->save();
        }
      }

      dispatch(new MailPodCreated( $pod, $podUser->user ));

      PodRepository::supercharge( $pod, $this->apiKey->user_id );

      return response([
        "pod" => $pod,
      ], 200 );

    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to create the pod. Please check your input and try again."]
      ], 500 );
    }

  }

  // [PATCH] /api/v1/pods
  // Updates pod data
  public function update( Request $request )
  {
    // validate input
    if ( !$this->validateUpdate( $request, $errors ) ) {
      return response([
        "errors" => $errors
      ], 409 );
    }

    try {
      $podData = $request->json()->get( 'pod' );
      $podId = $podData['id'];

      // allow patching user's pods only
      if ( !PodUser::where( 'user_id', $this->apiKey->user_id )->where( 'pod_id', $podId )->where( 'is_owner', 1 )->count() ) {
        return response([
          "errors" => ["Unauthorized. Pod doesn't belong to authenticated user"]
        ], 403 );
      }

      $pod = Pod::find( $podId );
      foreach( $podData as $fieldName => $fieldValue ) {
        switch ( $fieldName ) {
          case 'id':
            // do nothing
          break;

          case 'image':
            $pod->image_url = $this->updatePodImage( $podData['image'], $pod->id );
          break;

          case 'users':
            $userIds = $fieldValue;
			$podUsers = PodUser::where('pod_id', $pod->id)
			->where('is_owner', 0)
			->get();
			$podUserIds = array();
			foreach($podUsers as $tmp){
				$podUserIds[$tmp->user_id] = $tmp->is_approved;
			}
	
            $pod->users()->detach();
            if ( !in_array( $this->apiKey->user_id, $userIds ) ) {
              $userIds[] = $this->apiKey->user_id;
            }
            foreach( $userIds as $userId ) {
				$isApproved = $userId == $this->apiKey->user_id;
				if(isset($podUserIds[$userId])){
					$isApproved = $podUserIds[$userId];
				}
              $podUser = PodUser::create([
                'user_id'     => $userId,
                'pod_id'      => $pod->id,
                'is_owner'    => $userId == $this->apiKey->user_id,
                'is_approved' => $isApproved,
				'invite_token' => uniqid()
              ]);
			  if ( $userId != $this->apiKey->user_id && !$isApproved) {
			  // todo: send email to user to invite request
				dispatch( new MailPodInviteUser( $pod, $podUser, $this->apiKey->user_id));
			  }
            }
          break;

          default:
            $pod->$fieldName = $fieldValue;
          break;
        }
      }

      $pod->save();
    }
    catch( \Exception $exception ) {
      return response([
        "errors" => ["An unexpected error occurred trying to update the pod. Please check your input and try again."]
      ], 500 );
    }

    PodRepository::supercharge( $pod, $this->apiKey->user_id );

    return response([
      "pod" => $pod
    ], 200 );
  }
  
  public function filter( Request $request )
  {
    $filterData = $request->json()->get( 'filter' );

    $pods = PodRepository::filter( $filterData, $this->apiKey->user_id );

    return [
      'pods' => $pods
    ];
  }

  public function get( $id )
  {
    $pod = Pod::find( $id );

    if ( $pod ) {
      PodRepository::supercharge( $pod, $this->apiKey->user_id );

      return response([
        'pod' => $pod
      ], 200 );
    }
    else {
      return response([
        "errors" => [ "Pod not found." ]
      ], 404 );
    }
  }

  // [POST] /pods/{id}/users/join
  public function userJoin( $id )
  {
    $pod = Pod::find( $id );
    if ( $pod ) {
      try {
        $isApproved = $pod->is_private ? 0 : 1;

        $podUser = PodUser::where( 'pod_id', $pod->id )->where( 'user_id', $this->apiKey->user_id )->first();
        if ( !$podUser ) {
          $podUser = PodUser::create([
            'user_id'     => $this->apiKey->user_id,
            'pod_id'      => $pod->id,
            'is_owner'    => 0,
            'is_approved' => $isApproved
          ]);

          //Send email request for private pod.
          if ( $pod->is_private ) {
            dispatch( new MailPodJoinRequest( $pod, User::find( $this->apiKey->user_id ) ) );
          }
          else {
            // Send push notification...
            $ownerPodUser = PodUser::where( 'is_owner', 1 )->where( 'pod_id', $pod->id )->first();
            $notification = Notification::create([
              'type' => 2,
              'is_read' => 1,
              'pod_id' => $pod->id,
              'receiver_id' => $ownerPodUser->user_id,
              'user_id' => $this->apiKey->user_id
            ]);
            dispatch( new PodPushNotification($pod, $podUser, 1, $this->apiKey->user_id));
          }
        }
        return response([
          'pod_user' => $podUser,
        ], 200 );
      }
      catch( \Exception $exception ) {
        return response([
          "errors" => ["An unexpected error occurred trying to add the pod member. Please check your input and try again. Make sure the user is not already a member.", $exception->getMessage()]
        ], 500 );
      }
    }
    else {
      return response([
        "errors" => [ "Pod not found." ]
      ], 404 );
    }
  }

  // [POST] /pods/{podId}/users/{userId}/approval
  public function userApproval( $podId, $userId )
  {
    $pod = Pod::find( $podId );
    if ( $pod ) {
      try {
        // check authenticated user is the owner (can approve)
        $podOwner = 
          $pod->users()
              ->where( 'is_owner', 1 )
              ->where( 'user_id', $this->apiKey->user_id )
              ->first();
        
        if ( $podOwner ) {
          $podUser = 
            PodUser::where( 'user_id', $userId )
              ->where( 'pod_id', $pod->id )->first();
          
          if ( $podUser ) {
            $podUser->is_approved = 1;
            $podUser->save();

            dispatch( new MailPodJoinRequestApproval( $pod, $podUser->user, $podOwner->user ));
            
            return response([
              "pod_user" => $podUser
            ], 200 );
          }
          else {
            return response([
              "errors" => [ "User is not member of the POD." ]
            ], 404 );
          }
        }
        else {
          return response([
            "errors" => [ "Can't approve member. Authenticated user is not owner of the POD." ]
          ], 403 );
        }
      }
      catch( Exception $exception ) {
        return response([
          "errors" => ["An unexpected error occurred trying to approve the pod members. Please check your input and try again."]
        ], 500 );
      }
    }
    else {
      return response([
        "errors" => [ "Pod not found." ]
      ], 404 );
    }
  }

  // [DELETE] /pods/{podId}/users/{userId}
  public function userLeaves( $podId, $userId )
  {
    $podUser = PodUser::where( 'user_id', $userId )->where( 'pod_id', $podId )->first();
    if ( $podUser ) {
      try {
        // Send push notification...
        $ownerPodUser = PodUser::where( 'is_owner', 1 )->where( 'pod_id', $podId )->first();
        $notification = Notification::create([
          'type' => 3,
          'is_read' => 1,
          'pod_id' => $podId,
          'receiver_id' => $ownerPodUser->user_id,
          'user_id' => $this->apiKey->user_id
        ]);
        $pod = Pod::find( $podId );
        dispatch( new PodPushNotification($pod, $podUser, 2, $this->apiKey->user_id));

        dispatch( new MailPodUserRemoval( $pod, $podUser->user, $ownerPodUser->user ));

        PodUser::destroy( $podUser->id );
        return response( "", 204 );
      }
      catch( Exception $exception ) {
        return response([
          "errors" => ["An unexpected error occurred trying to remove the pod member. Please check your input and try again."]
        ], 500 );
      }
    }
    else {
      return response([
        "errors" => [ "Pod member not found." ]
      ], 404 );
    }
  }

  public function remove( $id )
  {
    Pod::destroy( $id );
    return response( "", 204 );
  }

  // After asking to join, the pod owner can approve using this link
  public function userApprovalLink( $podId, $userId, $approvalToken = null)
  {
	$status = true;
	$error = '';
	$pod = null;
	if(!empty($approvalToken)){
		$pod = Pod::where( 'id', $podId )->where( 'approval_token', $approvalToken )->first();
	}
	else{
		$pod = Pod::where( 'id', $podId )->first();
	}
    if ( $pod ){
      $podUser = PodUser::where( 'user_id', $userId )->where( 'pod_id', $pod->id )->first();
      $podOwner = PodUser::where( 'is_owner', 1 )->where( 'pod_id', $pod->id )->first();
      if ( $podUser ) {
        $podUser->is_approved = 1;
        $podUser->save();
        
        dispatch( new MailPodJoinRequestApproval( $pod, $podUser->user, $podOwner->user ));
      }
    }
	else{
		$status = false;
		$error = 'Pod not found!';
	}

    return View::make( 'pod_user_approval' , array(
		'status' => $status,
		'error' => $error
	));
  }

  // PRIVATE ==========================================

  private function validate( $request, &$errors )
  {
    $validation = Validator::make( $request->all(),
      [
        'pod.name'    => 'required|string|unique:pods,name',
      ],
      [
        'pod.name.required' => 'Pod name is required',
        'pod.name.string'   => 'Please provide a valid POD name',
        'pod.name.unique'   => 'Sorry, a pod with that name already exists'
      ]);

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    return !$errors;
  }

  private function validateUpdate( $request, &$errors )
  {
    $validation = Validator::make( $request->all(),
      [
        'pod.id'      => 'required|integer',
        'pod.name'    => 'string|unique:pods,name',
      ],
      [
        'pod.id.required'  => 'POD ID is required',
        'pod.id.integer'   => 'Please provide a valid POD id',
        'pod.name.string'  => 'Please provide a valid POD name',
        'pod.name.unique'  => 'Sorry, a pod with that name already exists'
      ]
    );

    if ( $validation->fails() ) {
      $errors = $validation->getMessageBag()->all();
    }

    return !$errors;
  }

  private function updatePodImage( $imageData, $podId )
  {
    $podImagePath = public_path() . '/img/pods/' . $podId . '.jpg';

    $fpImage = fopen( $podImagePath, 'w+' );
    fwrite( $fpImage, base64_decode( $imageData ) );
    fclose( $fpImage );

    return env( 'ASSETS_URL' ) .  '/img/pods/' . $podId . '.jpg';
  }
  
  public function userAcceptInviteRequest( $podId, $userId, $inviteToken = null)
  {
	$status = true;
	$error = '';
	$pod = null;
	if(empty($inviteToken)){
		return View::make( 'pod_user_accept' , array(
          'status' => false,
          'error' => 'Invite token can not be null.'
		));
	}
	
	$pod = Pod::where( 'id', $podId )->first();
	if(empty($pod)){
		return View::make( 'pod_user_accept' , array(
          'status' => false,
          'error' => 'Pod not found!'
		));
	}
	
    if ( $pod ){
      $podUser = PodUser::where( 'user_id', $userId )
        ->where('invite_token', $inviteToken)
        ->where( 'pod_id', $pod->id )
        ->first();
	  
      if (empty($podUser )) {
        return View::make( 'pod_user_accept' , array(
          'status' => false,
          'error' => 'No request found!'
		));
      }
	  
	  $podUser->is_approved = 1;
      $podUser->save();
      
      $podOwner = PodUser::where( 'is_owner', 1 )
        ->where( 'pod_id', $pod->id )
        ->first();
      
      dispatch( new MailPodInviteUserAcceptance( $pod, $podUser->user, $podOwner->user ));
    }

    return View::make( 'pod_user_accept' , array(
		'status' => $status,
		'error' => $error
	));
  }

}