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
	<a href='#' onclick='onButton(60);' class='WhiteKey'>A</a>
	<a href='#' onclick='onButton(61);' class='BlackKey cs'>W</a>
	<a href='#' onclick='onButton(62);' class='WhiteKey'>S</a>
	<a href='#' onclick='onButton(63);' class='BlackKey ds'>E</a>
	<a href='#' onclick='onButton(64);' class='WhiteKey'>D</a>
	<a href='#' onclick='onButton(65);' class='WhiteKey '>F</a>
	<a href='#' onclick='onButton(66);' class='BlackKey fs'>T</a>
	<a href='#' onclick='onButton(67);' class='WhiteKey'>G</a>
	<a href='#' onclick='onButton(68);' class='BlackKey gs'>Y</a>
	<a href='#' onclick='onButton(69);' class='WhiteKey'>H</a>
	<a href='#' onclick='onButton(70);' class='BlackKey as'>U</a>
	<a href='#' onclick='onButton(71);' class='WhiteKey'>J</a>
	<a href='#' onclick='onButton(72);' class='WhiteKey'>K</a>
	<a href='#' onclick='onButton(73);' class='BlackKey cs'>O</a>
	<a href='#' onclick='onButton(74);' class='WhiteKey'>L</a>
	<a href='#' onclick='onButton(75);' class='BlackKey ds'>P</a>
	<a href='#' onclick='onButton(76);' class='WhiteKey'>;</a>
	<a href='#' onclick='onButton(77);' class='WhiteKey'>'</a><!--
	<a href='#' onclick='onButton(78);' class='BlackKey'></a>
	<a href='#' onclick='onButton(79);' class='WhiteKey'></a>
	<a href='#' onclick='onButton(80);' class='BlackKey'></a>
	<a href='#' onclick='onButton(81);' class='WhiteKey'></a>
	<a href='#' onclick='onButton(82);' class='BlackKey'></a>
	<a href='#' onclick='onButton(83);' class='WhiteKey'></a>
	<a href='#' onclick='onButton(84);' class='WhiteKey'></a>-->
	</div>

	<pre id='csv' style='font-size: .5em;'></pre>
	<button id='searchCSV' class='btn btn-primary'>Search CSV</button>
</div>

<link rel="stylesheet" href="css/keyboard.css">
@endsection

@section('scripts')
<script type="text/javascript" src="js/keyboard.js"></script>
<script type="text/javascript" src="js/webmidi.min.js"></script>
@stop
