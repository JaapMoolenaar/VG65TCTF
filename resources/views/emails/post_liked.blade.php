@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $owner->getFirstName() }},<br>
    <br>
    <p>		
        <strong>{{ $user->getFullName() }}</strong> likes your post <strong>{{ $post->title }}</strong>.
    </p>
@endsection