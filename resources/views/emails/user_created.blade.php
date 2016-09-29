@extends('emails.layout.main')

@section('content')
  <p>Hi {{ $user->getFirstName() }},<br>
  <br>
  <p>		
    Welcome to Dolphin! You can start using the app right away.<br>
    <br>
    Login using the credentials below:<br>
    <table cellpadding="0" cellspacing="0">
        <tr>
            <th width="125">E-mail address</th>
            <td>:</td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>Password</th>
            <td>:</td>
            <td>{{ $password }}</td>
            <?php 
            /* or something like:
            <td><em>The password you chose during the registration process.</em></td>
            */ 
            ?>
        </tr>
    </table>
  </p>

  <?php
  /* In case you later want to approve e-mail adresses
  @include('emails.layout.button', [
      'url' => URL::to('/users/confirm-email-address/'.$conirm_token), 
      'label' => 'ACCEPT'
  ])
  */
  ?>
    
@endsection