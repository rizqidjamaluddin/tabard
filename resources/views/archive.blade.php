@extends('template')
@section('title', 'Archives')

@section('content')
    <div class="archive">
        <?php $i = count($entries); ?>
        @foreach($entries as $entry)
            <div class="article-archive">
            <h1><a href="{{ URL($entry->slug) }}"><span class="article-id">{{ $i }}</span>
                    {{ isset($entry->headline) ? $entry->headline : '' }}</a></h1>
            @if(isset($entry->excerpt))
                <p>{{ $entry->excerpt }}</p>
            @endif
            </div>
            <?php $i-- ?>
            @endforeach
    </div>
@stop