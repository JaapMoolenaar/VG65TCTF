@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $owner->getFirstName() }},<br>
    <br>
    <p>		
		<strong>{{ $user->getFullName() }}</strong> accepted your request to join your pod <strong>{{ $pod->name }}</strong>
    </p>
    
@endsection