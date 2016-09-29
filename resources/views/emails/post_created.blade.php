@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $owner->getFirstName() }},<br>
    <br>
    <p>		
        <strong>{{ $user->getFullName() }}</strong> created a new post <strong>{{ $post->title }}</strong>.
    </p>
@endsection