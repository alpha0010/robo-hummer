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

	<div class='keyboard'>
    <a class="Backspace" href='#'><span>&#x232B;</span></a>
	<a href='#' data-midi='60' class='WhiteKey'><span>A</span></a>
	<a href='#' data-midi='61' class='BlackKey cs'>W</a>
	<a href='#' data-midi='62' class='WhiteKey'><span>S</span></a>
	<a href='#' data-midi='63' class='BlackKey ds'>E</a>
	<a href='#' data-midi='64' class='WhiteKey'><span>D</span></a>
	<a href='#' data-midi='65' class='WhiteKey '><span>F</span></a>
	<a href='#' data-midi='66' class='BlackKey fs'>T</a>
	<a href='#' data-midi='67' class='WhiteKey'><span>G</span></a>
	<a href='#' data-midi='68' class='BlackKey gs'>Y</a>
	<a href='#' data-midi='69' class='WhiteKey'><span>H</span></a>
	<a href='#' data-midi='70' class='BlackKey as'>U</a>
	<a href='#' data-midi='71' class='WhiteKey'><span>J</span></a>
	<a href='#' data-midi='72' class='WhiteKey'><span>K</span></a>
	<a href='#' data-midi='73' class='BlackKey cs'>O</a>
	<a href='#' data-midi='74' class='WhiteKey'><span>L</span></a>
	<a href='#' data-midi='75' class='BlackKey ds'>P</a>
	<a href='#' data-midi='76' class='WhiteKey'><span>;</span></a>
	<a href='#' data-midi='77' class='WhiteKey'><span>'</span></a>
	<form><input type='text'></input></form>
	</div>

	<div id="staveInput"></div>
	<!--<p>Note: Relative note length is recorded. Changing BPM only changes rendering of input.</p>
	<form>
		<input id="bpmSlider" type="range" min='60' max='240' step="1" value="120"></input>
		<span id="bpmArea"></span>
	</form>-->
	<pre id='csv' style='font-size: .5em;'></pre>
	<button id='searchCSV' class='btn btn-primary'>Search</button>
	<div id='results'></div>
</div>

<link rel="stylesheet" href="css/keyboard.css">
@endsection

@section('scripts')
<script type="text/javascript" src="js/MIDI.js" defer></script>
<script type="text/javascript" src="js/keyboard.js" defer></script>
@endsection
