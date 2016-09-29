@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $user->getFirstName() }},<br>
    <br>
    <p>		
		<strong>{{ $owner->getFullName() }}</strong> added you as a member in pod <strong>{{ $pod->name }}</strong>. Just click the button below if you want to accept this request.
    </p>
    
    @include('emails.layout.button', [
        'url' => URL::to('/pods/accept/'.$pod->id.'/'.$user->id.'/'.$podUser->invite_token), 
        'label' => 'ACCEPT'
    ])
    
@endsection