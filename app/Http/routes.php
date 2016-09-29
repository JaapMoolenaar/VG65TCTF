<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get( '/', function () { return view( 'welcome' ); } );

Route::get( '/pods/approve/{podId}/{userId}/{approvalToken}', 'PodsController@userApprovalLink' );
Route::get( '/pods/approve/{podId}/{userId}', 'PodsController@userApprovalLink' );

Route::get( '/pods/accept/{podId}/{userId}/{inviteToken}', 'PodsController@userAcceptInviteRequest' );

Route::group(
  [
    'prefix' => 'api/v1',
    'middleware' => ['request.body.json']
  ],
  function () {
    // Auth
    Route::post( 'login', 'TokenController@byUserPass' );

    // Users
    Route::post(  'users',                     'UsersController@create' );
    Route::patch( 'users',                     'UsersController@update' );
    Route::post(  'users/filter',              'UsersController@filter' );
    Route::get(   'users/{id}',                'UsersController@get' );
    Route::get(   'users/{id}/comments',       'UsersController@getComments' );
    Route::get(   'users/{user}/likes/{post}', 'UsersController@getLikesByPost' );
    Route::get(   'users/{id}/likes',          'UsersController@getLikes' );

    // Posts
    Route::post(   'posts',               'PostsController@create' );
    Route::post(   'posts/filter',        'PostsController@filter' );
    Route::get(    'posts/{id}',          'PostsController@get' );
    Route::get(    'posts/{id}/likes',    'PostsController@getLikes' );
    Route::get(    'posts/{id}/comments', 'PostsController@getComments' );
    Route::post(   'posts/{id}/likes',    'PostsController@postLike' );
    Route::delete( 'posts/{id}/likes',    'PostsController@deleteLike' );
    Route::post(   'posts/{id}/comments', 'PostsController@postComment' );
    Route::delete( 'posts/{id}',          'PostsController@remove' );

    // Post Abuse Reports
    Route::post( 'posts/{id}/reports', 'PostsController@postReport' );

    // Topics
    Route::get(    'topics/user/{userId}', 'TopicsController@byUser' );
    Route::post(   'topics',               'TopicsController@create' );
    Route::post(   'topics/filter',        'TopicsController@filter' );
    Route::delete( 'topics/{id}',          'TopicsController@remove' );

    // Subjects
    Route::get(  'subjects', 'SubjectsController@getAll' );
    Route::post( 'subjects', 'SubjectsController@create' );

    // Grades
    Route::get(  'grades', 'GradesController@getAll' );
    Route::post( 'grades', 'GradesController@create' );

    // PODs
    Route::post(   'pods',                                  'PodsController@create' );
    Route::patch(  'pods',                                  'PodsController@update' );
    Route::post(   'pods/filter',                           'PodsController@filter' );
    Route::get(    'pods/{id}',                             'PodsController@get' );
    Route::delete( 'pods/{id}',                             'PodsController@remove' );
    Route::post(   'pods/{id}/users/join',                  'PodsController@userJoin' );
    Route::post(   'pods/{podId}/users/{userId}/approval',  'PodsController@userApproval' );
    Route::delete( 'pods/{podId}/users/{userId}',           'PodsController@userLeaves' );

    //Notifications
    Route::post(   'notifications/filter',        'NotificationsController@filter' );


//    DEPRECATED / UNTESTED --------------------------------------------------------------------------------------------

//    Route::get( 'token/by-device-id/{deviceId}', 'TokenController@byDeviceId' );

//    Route::post( 'subjects',        'SubjectsController@create' );
//    Route::delete( 'subjects/{id}', 'SubjectsController@remove' );

//    Route::post( 'grades',        'GradesController@create' );
//    Route::delete( 'grades/{id}', 'GradesController@remove' );

  }
);
