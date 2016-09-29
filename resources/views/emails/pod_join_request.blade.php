@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $owner->getFirstName() }},<br>
    <br>
    <p>		
        <strong>{{ $user->getFullName() }}</strong> from <strong>{{ $user->location }}</strong> wants to join your POD <strong>{{ $pod->name }}</strong>. Just click the button below if you want to approve access.
    </p>
    
    @include('emails.layout.button', [
        'url' => URL::to('/pods/approve/'.$pod->id.'/'.$user->id.'/'.$pod->approval_token), 
        'label' => 'ACCEPT'
    ])
    
@endsection