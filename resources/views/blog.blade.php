<!DOCTYPE html>
<html>
<head>
    <script src="//use.typekit.net/xvp4hag.js"></script>
    <script>try{Typekit.load();}catch(e){}</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/prism.css') }}"/>
    <title> @if(isset($meta->title)) {{ $meta->title }} &bull; @endif The Voices Made Me Write It In PHP</title>
</head>
<body>

<div class="content language-php">
@include('body')


    <p class="links">
        @if ($newer)
            <a href="{{ url($newer) }}">&larr; Newer</a>
        @endif
        @if ($older)
            <a href="{{ url($older) }}" class="older-link">Older &rarr;</a>
        @endif
    </p>

    <p class="footer">
        Now if you'll excuse me, I've got a memory leak to attend to...
        Why exactly did I write a long-running process in PHP again?
    </p>
</div>

{{--<script src="{{ asset('jquery-1.11.2.js') }}"></script>--}}
{{--<script>--}}

{{--</script>--}}
<script src="{{ asset('prism.js') }}"></script>
</body>

</html>