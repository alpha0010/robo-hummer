$(document).ready(function(){
	updateStave();
});

/**
 * @brief Make the stave match the state of the csv.
 */
function updateStave(){
	var notes = "";
	var totalTime = 0;


	var ln = {
		1 : 'w',
		.5 : 'h',
		.25 : 'q',
		.125 : '8',
	};

	var lines = $("#csv").text().split('\n');
	for(var i = 0;i < lines.length;i++){
		var note = lines[i].split(',')
		if ( MIDI.noteToKey[note[0]] != undefined ){
			var gamut = MIDI.noteToKey[note[0]];
			var time = getLength(parseInt(note[1]))
			var length = ln[time]
			notes += gamut + "/" + length +  ",";
			totalTime += time
		}
	}

	$("#staveInput").text("");

	var vf = new Vex.Flow.Factory({
		renderer: {elementId: 'staveInput', width: 500, height: 200}
	});

	var score = vf.EasyScore();
	var system = vf.System();

	system.addStave({
		voices: [
			score.voice(score.notes(notes, {stem: 'up'}), {time: totalTime * 8 + '/8'}),
		]
	}).addClef('treble');
	vf.draw();
}


/**
 * @brief take in milliseconds and return fractions of a beat.
 */
function getLength( milliseconds ){
	// BPM = 120bpm, 500ms is a quarter note, 2s is a whole note.
	var wholeLength = 2000;
	var fraction = milliseconds / wholeLength;
	if ( fraction >= 1 ) return 1;
	else if ( fraction >= .5 ) return .5;
	else if ( fraction >= .25 ) return .25;
	else return .125;
}
