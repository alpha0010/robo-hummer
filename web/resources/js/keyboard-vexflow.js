/* global MIDI */
// TODO: Don't load MIDI into the global namespace.
var Vex = require('vexflow');
var $ = window.jQuery;

$(document).ready(function () {
  updateStave();
  $('#bpmSlider').change(function (val) {
    updateStave($(this).val() / 0.06);
    $('#bpmArea').text('bpm: ' + $(this).val());
  });
});

/**
 * @brief Make the stave match the state of the csv.
 */
function updateStave (wholeLength = 2000) {
  var notes = [];

  var ln = {
    1: 'w',
    0.75: 'h.',
    0.5: 'h',
    0.375: 'q.',
    0.25: 'q',
    0.1875: '8.',
    0.125: '8',
    0.0625: '16'
  };

  var notesPerSection = 8;

  for (let i = 0; i < window.list.length; i++) {
    let j = parseInt(i / notesPerSection);
    if (j === i / notesPerSection) {
      notes.push({ notestring: '', time: 0 });
    }
    var note = window.list[i];
    if (MIDI.noteToKey[note[0]] !== undefined) {
      var gamut = MIDI.noteToKey[note[0]];
      var time = getLength(parseInt(note[1]), wholeLength);
      var length = ln[time];

      notes[j].notestring += gamut + '/' + length + ',';
      notes[j].time += time;
    }
  }
  $('#staveInput').text('');

  for (let i = 0; i < notes.length; i++) {
    let $div = $("<div id='staveInput" + i + "'>");
    $('#staveInput').append($div);

    var vf = new Vex.Flow.Factory({
      renderer: { elementId: 'staveInput' + i, width: 500, height: 125 }
    });

    var score = vf.EasyScore();
    var system = vf.System();

    system
      .addStave({
        voices: [
          score.voice(score.notes(notes[i].notestring, { stem: 'up' }), {
            time: notes[i].time * 16 + '/16'
          })
        ]
      })
      .addClef('treble');
    vf.draw();
  }
  // Trim off the treble clef from all but the first stave.
  var width = 400;
  $('#staveInput svg')
    .attr('viewBox', '50 25 450 100')
    .attr('width', width * 0.9)
    .attr('height', width * 0.25);
  $('#staveInput div:first-of-type svg')
    .attr('viewBox', '0 25 500 100')
    .attr('width', width)
    .attr('height', width * 0.25);
}

/**
 * @brief take in milliseconds and return fractions of a beat.
 */
function getLength (milliseconds, wholeLength = 2000) {
  // BPM = 120bpm, 500ms is a quarter note, 2s is a whole note.
  var fraction = milliseconds / wholeLength;
  if (fraction >= 1) return 1;
  else if (fraction >= 0.75) return 0.75;
  else if (fraction >= 0.5) return 0.5;
  else if (fraction >= 0.375) return 0.375;
  else if (fraction >= 0.25) return 0.25;
  else if (fraction >= 0.1875) return 0.1875;
  else if (fraction >= 0.125) return 0.125;
  else return 0.0625;
}

window.updateStave = updateStave;
