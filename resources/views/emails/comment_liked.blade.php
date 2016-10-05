@extends('emails.layout.main')

@section('content')
    <p>Hi {{ $owner->getFirstName() }},<br>
    <br>
    <p>		
        <strong>{{ $user->getFullName() }}</strong> likes your comment on the post <strong>{{ $post->title }}</strong>.
    </p>
@endsection