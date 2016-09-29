@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $user->getFirstName() }},<br>
    <br>
    <p>		
        You have been removed from the POD <strong>{{ $pod->name }}</strong>.
    </p>
    
@endsection