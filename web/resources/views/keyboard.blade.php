@extends('layouts.app')

@section('content')
<!--	<object id='Jazz1' classid='CLSID:1ACE1618-1C7D-4561-AEE1-34842AA85E90' class='hidden' style='display:none'>
			<object id='Jazz2' type='audio/x-jazz' class='hidden'></object>
		</object>
		-->
<div class='content'>
	<h5>Works best in Chrome.
		<!--For other browser support, consider installing the <a href='http://jazz-soft.net/download/Jazz-Plugin/'>Jazz-Plugin</a>-->
	</h5>
	<a href='#' onclick='setup();'>Relink</a>
	<p id='status'></p>
	<div id='dropdowns'></div>

	<div class='keyboard'>
	<a href='#' data-midi='60' class='WhiteKey'>A</a>
	<a href='#' data-midi='61' class='BlackKey cs'>W</a>
	<a href='#' data-midi='62' class='WhiteKey'>S</a>
	<a href='#' data-midi='63' class='BlackKey ds'>E</a>
	<a href='#' data-midi='64' class='WhiteKey'>D</a>
	<a href='#' data-midi='65' class='WhiteKey '>F</a>
	<a href='#' data-midi='66' class='BlackKey fs'>T</a>
	<a href='#' data-midi='67' class='WhiteKey'>G</a>
	<a href='#' data-midi='68' class='BlackKey gs'>Y</a>
	<a href='#' data-midi='69' class='WhiteKey'>H</a>
	<a href='#' data-midi='70' class='BlackKey as'>U</a>
	<a href='#' data-midi='71' class='WhiteKey'>J</a>
	<a href='#' data-midi='72' class='WhiteKey'>K</a>
	<a href='#' data-midi='73' class='BlackKey cs'>O</a>
	<a href='#' data-midi='74' class='WhiteKey'>L</a>
	<a href='#' data-midi='75' class='BlackKey ds'>P</a>
	<a href='#' data-midi='76' class='WhiteKey'>;</a>
	<a href='#' data-midi='77' class='WhiteKey'>'</a><!--
	<a href='#' data-midi='78' class='BlackKey'></a>
	<a href='#' data-midi='79' class='WhiteKey'></a>
	<a href='#' data-midi='80' class='BlackKey'></a>
	<a href='#' data-midi='81' class='WhiteKey'></a>
	<a href='#' data-midi='82' class='BlackKey'></a>
	<a href='#' data-midi='83' class='WhiteKey'></a>
	<a href='#' data-midi='84' class='WhiteKey'></a>-->
	<form><input type='text'></input></form>
	</div>

	<pre id='csv' style='font-size: .5em;'></pre>
	<button id='searchCSV' class='btn btn-primary'>Search CSV</button>
	<div id='results'></div>
</div>

<link rel="stylesheet" href="css/keyboard.css">
@endsection

@section('scripts')
<script type="text/javascript" src="js/MIDI.js" defer></script>
<script type="text/javascript" src="js/keyboard.js" defer></script>
@endsection
