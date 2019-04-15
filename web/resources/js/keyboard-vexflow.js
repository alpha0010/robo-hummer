/* global MIDI */
// TODO: Don't load MIDI into the global namespace.
import Vex from 'vexflow';
import $ from 'jquery';

export class VexFlowRenderer {
  constructor () {
    this.ln = {
      1: 'w',
      0.75: 'h.',
      0.5: 'h',
      0.375: 'q.',
      0.25: 'q',
      0.1875: '8.',
      0.125: '8',
      0.0625: '16'
    };
  }
  /**
   * @brief Make the stave match the state of the csv.
   */
  update (list, selector, wholeLength = 2000) {
    var notes = [];

    var notesPerSection = 8;

    for (let i = 0; i < list.length; i++) {
      let j = Math.floor(i / notesPerSection);
      if (i % notesPerSection === 0) {
        notes.push({ notestring: '', time: 0 });
      }
      var note = list[i];
      if (MIDI.noteToKey[note[0]] !== undefined) {
        var gamut = MIDI.noteToKey[note[0]];
        var time = this.getLength(parseInt(note[1]), wholeLength);
        var length = this.ln[time];

        notes[j].notestring += gamut + '/' + length + ',';
        notes[j].time += time;
      }
    }
    $(selector).text('');

    for (let i = 0; i < notes.length; i++) {
      let $div = $(`<div id='staveInput${i}'>`);
      $(selector).append($div);

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
    $(selector + ' svg')
      .attr('viewBox', '50 25 450 100')
      .attr('width', width * 0.9)
      .attr('height', width * 0.25);
    $(selector + ' div:first-of-type svg')
      .attr('viewBox', '0 25 500 100')
      .attr('width', width)
      .attr('height', width * 0.25);
  }

  /**
   * @brief take in milliseconds and return fractions of a beat.
   */
  getLength (milliseconds, wholeLength = 2000) {
    // BPM = 120bpm, 500ms is a quarter note, 2s is a whole note.
    var fraction = milliseconds / wholeLength;

    // Get the fractions of allowed notes largest to smallest.
    let bounds = Object.keys(this.ln).sort((a, b) => b - a);
    // "Round" our fraction down to the nearest bound.
    let result = bounds.find(bound => fraction >= bound);
    // Return last bound if the note was shorter than all bounds.
    result = result === undefined ? bounds.pop() : result;
    return parseFloat(result);
  }
}
