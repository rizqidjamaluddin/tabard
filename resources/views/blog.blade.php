@extends('template')

@if(isset($meta->title))
    @section('title')
        {{ $meta->title }} &bull;
    @stop
@endif


@section('content')

    <p class="util">
        <a href="{{ url($meta->slug) }}">Permalink</a>
        @if(isset($meta->date))
            &bull; {{ date("F j, Y", $meta->date) }}
        @endif
    </p>

@include('body')

    <p class="links">
        @if ($newer)
            <a href="{{ url($newer) }}">&larr;
                Newer @if (isset($newerMeta->title)) &bull; <strong>{{$newerMeta->title}}</strong> @endif
            </a>
        @endif
        @if ($older)
            <a href="{{ url($older) }}" class="older-link">
                Older @if (isset($olderMeta->title)) &bull; <strong>{{$olderMeta->title}}</strong> @endif &rarr;
            </a>

            @endif
    </p>

    <p class="footer">
        Now if you'll excuse me, I've got a memory leak to attend to...
        Why exactly did I write a long-running process in PHP again?
    </p>

@stop