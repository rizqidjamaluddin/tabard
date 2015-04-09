<!DOCTYPE html>
<html>
<head>
    <script src="//use.typekit.net/xvp4hag.js"></script>
    <script>try{Typekit.load();}catch(e){}</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/prism.css') }}"/>
    <title> @if(isset($meta->title)) {{ $meta->title }} &bull; @endif The Voices Made Me Write It In Laravel</title>
</head>
<body>

<div class="content language-php">
{!! $post !!}


    <p>
        <a href="{{ url($previous) }}">Previous</a>
        <a href="{{ url($next) }}">Next</a>
    </p>
</div>

<script src="{{ asset('prism.js') }}"></script>
</body>

</html>