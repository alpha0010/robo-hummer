/**
 * keyboard.js operates the keyboard using the interface.
 */
var csv = '';
var current = {'note': -1, 'date':Date.now()};
var previous = {'note': -1, 'date':Date.now()};

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

$(document).ready(function()
{
	$('.keyboard').keydown( function(e){
		/* Use codes since they are layout agnostic */
		if ( codes[e.originalEvent.code] != undefined )
		{
			onButton(codes[e.originalEvent.code]);
			e.preventDefault();
		}
	});

	$('#searchCSV').click( function(e){
		$.post(
			'/api/uploadCSV',
			$('#csv').text(),
			showResults
		);
	});
});

function showResults( results )
{
	$('#results').text("");
	for( i = 0; i < results.length; i++ )
	{
		var filename = results[i]['name'].match( /[^/]*$/ )[0];
		var hymnal = filename.match( /^[^-]*/ )[0];
		var number = filename.split('-')[1].split('.')[0];
		var reslink = "<a href='https://hymnary.org/hymn/" + hymnal + "/" + number + "'>"
			+ filename + "</a><br/>";
		$('#results').append( reslink );
	}
}
