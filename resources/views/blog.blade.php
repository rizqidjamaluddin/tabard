<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}"/>
    <title> @if(isset($meta->title)) {{ $meta->title }} &bull; @endif The Voices Made Me Write It In Laravel</title>
</head>
<body>

<div class="content">
{!! $post !!}


    <p>
        <a href="{{ url($previous) }}">Previous</a>
        <a href="{{ url($next) }}">Next</a>
    </p>
</div>

</body>

</html>