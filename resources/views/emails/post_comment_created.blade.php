@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $owner->getFirstName() }},<br>
    <br>
    <p>		
        <strong>{{ $user->getFullName() }}</strong> commented on your post <strong>{{ $post->title }}</strong>:<br>
        <em>{{ str_limit($comment->body, 120) }}</em>
    </p>
@endsection