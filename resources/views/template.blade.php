<!DOCTYPE html>
<html>
<head>
    <script src="//use.typekit.net/xvp4hag.js"></script>
    <script>try{Typekit.load();}catch(e){}</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/prism.css') }}"/>
    <title> @yield('title', '') The Voices Made Me Write It In PHP</title>
</head>
<body>

<div class="content language-php">

    <div class="header">
        You're reading
        <div class="name">&#12300; <a href="{{ url('/') }}">The Voices Made Me Write It In PHP</a> &#12301;</div>
        a compendium of Rizqi&rsquo;s misadventures in design &amp; coding.
        <div class="menu"><a href="{{ url('archive') }}">Archives</a> &bull; <a href="http://github.com/rizqidjamaluddin/tabard">Source on Github</a></div>
    </div>

    @yield('content', '')

</div>

<script src="{{ asset('prism.js') }}"></script>
</body>

</html>