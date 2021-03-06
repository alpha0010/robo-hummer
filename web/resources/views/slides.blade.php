<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <script src="{{ asset('js/slides.js') }}" defer></script>
    <link href="{{ asset('css/reveal.css') }}" rel="stylesheet">
</head>
<body>
    <div class="reveal">
        <div class="slides">
            @foreach ($slides as $slide)
                <section id="{{ $slide['name'] }}">
                    {!! $slide['content'] !!}
                </section>
            @endforeach
        </div>
    </div>
</body>
</html>
