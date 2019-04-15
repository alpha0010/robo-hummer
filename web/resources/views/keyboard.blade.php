@extends('layouts.app')

@section('content')
<div class='container'>
    <h2>{{ config('app.name') }} Melody Search</h2>

    <p>
        Search for a song by entering a snippet of its melody. Either click on
        the virtual keyboard, or type the letters displayed. Search does take
        into account both note pitch and duration, but is forgiving as long as
        your are <em>close enough</em>.
    </p>
    <p>
        Click the box below to enable typing input.
    </p>

    <div id='melodySearch'></div>
</div>

<link rel="stylesheet" href="css/keyboard.css">
@endsection

@section('scripts')
<script type="text/javascript" src="js/MIDI.js" defer></script>
<script type="text/javascript" src="js/keyboard-usage.js" defer></script>
@endsection
