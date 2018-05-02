/**
 * keyboard.js operates the keyboard using the interface.
 */
var csv = '';
var current = [];
var previous = [];

function onButton(note){
	console.log(note);
	current.note = note;
	current.date = Date.now();//gives time in milliseconds since epoch.
	length = current.date - previous.date;
	var csv = previous.note + "," + length + "\n";
	previous.note = note;
	previous.date = current.date;
	/* Store it in the DOM */
	$("#csv").append(csv);
}

var codes = {
	'KeyA': 60,
	'KeyW': 61,
	'KeyS': 62,
	'KeyE': 63,
	'KeyD': 64,
	'KeyF': 65,
	'KeyG': 66,
	'KeyY': 67,
	'KeyH': 68,
	'KeyU': 69,
	'KeyJ': 70,
	'KeyK': 71,
	'KeyO': 72,
	'KeyL': 73,
	'KeyP': 74,
	'Semicolon': 75,
	'Quote': 76,
};

$('.keyboard').keydown( function(e){
	/* Use codes since they are layout agnostic */
	if ( codes[e.originalEvent.code] != undefined )
	{
		onButton(codes[e.originalEvent.code]);
		e.preventDefault();
	}
});
