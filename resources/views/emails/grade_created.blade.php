@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $owner->getFirstName() }},<br>
    <br>
    <p>		
        Your new grade <strong>{{ $grade->name }}</strong> is ready for use!
    </p>
@endsection