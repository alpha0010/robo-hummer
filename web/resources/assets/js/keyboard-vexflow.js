$(document).ready(function(){
	updateStave();
	$('#bpmSlider').change(function(val){
		updateStave($(this).val()/0.06);
		$('#bpmArea').text("bpm: " + $(this).val());
	});
});

/**
 * @brief Make the stave match the state of the csv.
 */
function updateStave(wholeLength = 2000){
	var notes = "";
	var totalTime = 0;

	var ln = {
		1 : 'w',
		.75 : 'h.',
		.5 : 'h',
		.375 : 'q.',
		.25 : 'q',
		.1875 : '8.',
		.125 : '8',
		.0625 : '16',
	};

	var lines = $("#csv").text().split('\n');
	for(var i = 0;i < lines.length;i++){
		var note = lines[i].split(',')
		if ( MIDI.noteToKey[note[0]] != undefined ){
			var gamut = MIDI.noteToKey[note[0]];
			var time = getLength(parseInt(note[1]), wholeLength)
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
			score.voice(score.notes(notes, {stem: 'up'}), {time: totalTime * 16 + '/16'}),
		]
	}).addClef('treble');
	vf.draw();
}


/**
 * @brief take in milliseconds and return fractions of a beat.
 */
function getLength( milliseconds, wholeLength = 2000 ){
	// BPM = 120bpm, 500ms is a quarter note, 2s is a whole note.
	var fraction = milliseconds / wholeLength;
	if ( fraction >= 1 ) return 1;
	else if ( fraction >= .75 ) return .75;
	else if ( fraction >= .5 ) return .5;
	else if ( fraction >= .375 ) return .375;
	else if ( fraction >= .25 ) return .25;
	else if ( fraction >= .1875 ) return .1875;
	else if ( fraction >= .125 ) return .125;
	else return .0625;
}
