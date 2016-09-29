@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $user->getFirstName() }},<br>
    <br>
    <p>		
        Your request to join the POD <strong>{{ $pod->name }}</strong> has been approved!
    </p>
@endsection