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
use Illuminate\Foundation\Testing\CrawlerTrait;

class Tester {
    use CrawlerTrait;
    
    protected $baseUrl = 'http://dolphin-api.local';
    
    public function test($method, $uri, $content, $token = null) {
        $this->app = app();
        
        /*
        $uri = '/api/v1/login';
        $method = 'POST';
        $content = [
            'username' => '',
            'password' => '',
        ];
        */
        
        $parameters = [];
        $cookies = [];
        $files = [];
        $server = [];
        $headers = [];
        
        if (null !== $token) {
            $headers['X-Authorization'] = $token;
        }
        
        echo "$method {$this->baseUrl}$uri<br>";
        
        $this->json($method, $uri, $content, $headers);
        
        $content = (string)$this->response->getContent();
        if (!trim($content)) {
          return;
        }
        
        $return = json_decode((string)$this->response->getContent());
        if (null !== $return) {
          if (isset($return->errors)) {
            dd($return);
          }
          return $return;
        }
        
        echo (string)$this->response->getContent();
        die();
    }
}

//Route::get( '/', function () { return view( 'welcome' ); } );
Route::get( '/', function () {  
    /*
    $user = DolphinApi\User::find(191);
    \Mail::send( 'emails.user_created', ['user' => $user, 'password' => '1234'], function ( $message ) use ($user) {
      $message->to( $user->email, $user->getFullName() )->subject( 'Welcome' );
    });
    */
    
    \DB::table('users')->where('username' , 'like', '%Satsume%')->delete();
    
    $data = with(new Tester)->test('POST', '/api/v1/users', [
        'user' => [
            'device_id'    => '1234',
            'device_token' => '1234',
            'email'        => 'satsume'.rand(1000000, 9999999).'@example.com',
            'password'     => '1234',
            "username"     => 'Satsume1',
            "first_name"   => 'Satsume first-',
            "last_name"    => 'and Last name',
//            "location"     => '',
//            "city"         => '',
//            "country"      => '',
//            "zip"          => '',        
//            "gender"       => 0,
//            "is_private"   => 0
        ]
      ]);
    
    echo "Created user {$data->user->id}: {$data->user->username}<br>";
    
    $data = with(new Tester)->test('POST', '/api/v1/users', [
        'user' => [
            'device_id'    => '12345',
            'device_token' => '12345',
            'email'        => 'satsume'.rand(1000000, 9999999).'@example.com',
            'password'     => '12345',
            "username"     => 'Satsume2',
//            "first_name"   => '',
//            "last_name"    => '',
//            "location"     => '',
//            "city"         => '',
//            "country"      => '',
//            "zip"          => '',        
//            "gender"       => 0,
//            "is_private"   => 0
        ]
      ]);
    
    echo "Created user {$data->user->id}: {$data->user->username}<br>";
    
    $data = with(new Tester)->test('POST', '/api/v1/users', [
        'user' => [
            'device_id'    => '123456',
            'device_token' => '123456',
            'email'        => 'satsume'.rand(1000000, 9999999).'@example.com',
            'password'     => '123456',
            "username"     => 'Satsume3',
            "first_name"   => 'Satsume3 first name',
            "last_name"    => 'Satsume3 last name',
//            "location"     => '',
//            "city"         => '',
//            "country"      => '',
//            "zip"          => '',        
//            "gender"       => 0,
//            "is_private"   => 0
        ]
      ]);
    
    echo "Created user {$data->user->id}: {$data->user->username}<br>";
    
    $data = with(new Tester)->test('POST', '/api/v1/users', [
        'user' => [
            'device_id'    => '1234567',
            'device_token' => '1234567',
            'email'        => 'satsume'.rand(1000000, 9999999).'@example.com',
            'password'     => '1234567',
            "username"     => 'Satsume4',
//            "first_name"   => '',
//            "last_name"    => '',
//            "location"     => '',
//            "city"         => '',
//            "country"      => '',
//            "zip"          => '',        
//            "gender"       => 0,
//            "is_private"   => 0
        ]
      ]);
        
    echo "Created user {$data->user->id}: {$data->user->username}<br>";
    
    
    $data = with(new Tester)->test('POST', '/api/v1/login', [
        'login' => [
            'username' => 'Satsume1',
            'password' => '1234',
        ]
    ]);
    $token = $data->token;
    $user = $data->user;

    
    $data = with(new Tester)->test('POST', '/api/v1/login', [
        'login' => [
            'username' => 'Satsume2',
            'password' => '12345',
        ]
    ]);
    $token2 = $data->token;
    $user2 = $data->user;
    
    
    $data = with(new Tester)->test('POST', '/api/v1/login', [
        'login' => [
            'username' => 'Satsume3',
            'password' => '123456',
        ]
    ]);
    $token3 = $data->token;
    $user3 = $data->user;
    
    
    $data = with(new Tester)->test('POST', '/api/v1/login', [
        'login' => [
            'username' => 'Satsume4',
            'password' => '1234567',
        ]
    ]);
    $token4 = $data->token;
    $user4 = $data->user;
    

    // create private POD with token 1 (Satsume)
    // also invite Satsume 3, whom should receive an invitation e-mail
    $data = with(new Tester)->test('POST', '/api/v1/pods', [
        'pod' => [
            'name'           => 'My pod name #'.rand(1000000, 9999999),
            'description'    => 'Just running some tests!',
            'is_private'     => 1,
            'image_width'    => 0,
            'image_height'   => 0,
            'users'          => [$user3->id, $user4->id],
        ]
    ], $token);
    
    $podid = $data->pod->id;
   
    echo 'Created pod #'.$podid.'<br>';

    
    // join private POD with token 2 (Satsume 2 joins Satsume 1)
    // Satsume 1 should get a mail with Satsume 2's request
    $data = with(new Tester)->test('POST', '/api/v1/pods/'. $podid .'/users/join', [], $token2);
    
    echo 'Sent Satsume 2 join request<br>';
    
    $data = with(new Tester)->test('DELETE', '/api/v1/pods/'. $podid .'/users/'. $user4->id, [], $token);
    
    echo 'Removed Satsume 4 from pod<br>';
    
    
    // I can apperantly create a post, eventhough I am not approved? Satsume 3 isn't
    $data = with(new Tester)->test('POST', '/api/v1/posts', [
        'post' =>
        [
            'title'        => 'My post title name #'.rand(1000000, 9999999),
            'body'         => 'My post body',
            'type'         => 'text',
            'pod_id'       => $podid,
        ]
    ], $token3);
    
    $post = reset($data->post);
    $postid = $post->id;
  
    echo 'Created post #'.$postid.'<br>';
    
    
    // Like the newly created post
    $data = with(new Tester)->test('POST', '/api/v1/posts/'.$postid.'/likes', [], $token);
    $data = with(new Tester)->test('POST', '/api/v1/posts/'.$postid.'/likes', [], $token2);
    
    // Comment on the newly created post
    $data = with(new Tester)->test('POST', '/api/v1/posts/'.$postid.'/comments', [
        'comment' => [
            'body' => 'This is my comment on the new post "'.$post->title.'". I\'ll babble a little to reach a miminum of 120 characters, so that the string gets truncated.'
        ]
    ], $token);
            
    dd($data);
    /*
    $data = with(new Tester)->test('POST', '/api/v1/users', [
        'user' => [
            'device_id'    => '1234',
            'device_token' => '1234',
            'email'        => 'satsume@example.com',
            'password'     => '1234',
            "username"     => 'Satsume',
//            "first_name"   => '',
//            "last_name"    => '',
//            "location"     => '',
//            "city"         => '',
//            "country"      => '',
//            "zip"          => '',        
//            "gender"       => 0,
//            "is_private"   => 0
        ]
      ]);
    */   
});

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
