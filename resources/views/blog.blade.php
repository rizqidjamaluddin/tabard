<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
    <title> @if(isset($meta->title)) {{ $meta->title }} &bull; @endif The Voices Made Me Write It In Laravel</title>
</head>
<body>

{!! $post !!}


<a href="{{ url($previous) }}">Previous</a>
<a href="{{ url($next) }}">Next</a>

</body>

</html>