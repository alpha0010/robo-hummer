
var lastNumber = 0;
var lastEmo = 0;
var input;
var output;
var renderedcsvnotes;
var playingi;
var playingdelay = 100;

//output.playNote("D1", 15, {duration: 2000, velocity: 125/127});
// Enable WebMidi.js
WebMidi.enable( setup );
function setup(err) {

	if (err) {
		console.log("WebMidi could not be enabled.", err);
		$("#status").text( "WebMidi could not be enabled. " + err );
		return 1;
	}

	// Viewing available inputs
	console.log(WebMidi.inputs);
	
	// Retrieve an input by index
	input = WebMidi.inputs[0];
	output = WebMidi.outputs[0];
	clearall();
	
	if ( ! input )
	{
		console.log( "no input devices found" );
		$("#status").text( "no input devices found" );
		return 2;
	}
	else
	{
		var ddhtml = "<select onchange='var v = $( this ).val();changeinput(v);'>";
		for (var i = 0; i < WebMidi.inputs.length; i++) {
			var name = WebMidi.inputs[i]._midiInput.manufacturer + ' ' + WebMidi.inputs[i]._midiInput.name
			ddhtml +="<option value='"+i+"'>Input: " + name + "</option>"
		}
		ddhtml +="</select>"
		$("#dropdowns").html(ddhtml);
	}
	if ( ! output )
	{
		console.log( "no output devices found" );
		$("#status").text( "no output devices found" );
		return 2;
	}
	else
	{
		var ddhtml = "<select onchange='var v = $( this ).val();changeoutput(v);'>";
		for (var i = 0; i < WebMidi.outputs.length; i++) {
			var name = WebMidi.outputs[i]._midiOutput.manufacturer + ' ' + WebMidi.outputs[i]._midiOutput.name
			ddhtml +="<option value='"+i+"'>Output: " + name + "</option>"
		}
		ddhtml +="</select>"
		$("#dropdowns").append(ddhtml);
	}
	setupinput()
}
function setupinput(){
	// Clear all listeners
	input.removeListener();

	// Listen for a 'note on' message on all channels
	input.addListener('noteon', "all",
		function(e){onButton(e.note.number);}
	);
}

function changeinput(i){
	input = WebMidi.inputs[i];
	setupinput()
	if ( !input ){
		ohno("Couldn't change input")
		if (i != 0) changeinput(0);
	}
}
function changeoutput(i){
	output = WebMidi.outputs[i];
	if ( !output ){
		ohno("Couldn't change output")
		if (i != 0) changeoutput(0);
	}
}
function ohno(text){
	alert(text)
}

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
