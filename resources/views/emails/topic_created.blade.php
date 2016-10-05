@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $owner->getFirstName() }},<br>
    <br>
    <p>		
        Your new topic <strong>{{ $topic->name }}</strong> is ready for use!
    </p>
@endsection