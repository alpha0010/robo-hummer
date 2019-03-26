/* global MIDI, updateStave */
// TODO: Don't load MIDI into the global namespace.

/**
 * keyboard.js operates the keyboard using the interface.
 */

require('./keyboard-vexflow');

var current = { note: -1, date: Date.now() };
var previous = { note: -1, date: Date.now() };
var $ = window.jQuery;
var list = [];
window.list = list;

function onButton (note) {
  current.note = note;
  current.date = Date.now(); // gives time in milliseconds since epoch.
  var length = current.date - previous.date;
  if (list.length > 0 && list[list.length - 1][1] === 0) {
    list[list.length - 1][1] = length;
  }

  if (previous.note !== -1) {
    MIDI.noteOff(0, previous.note, 0, 0);
  }
  MIDI.noteOn(0, current.note, 100, 0);

  previous.note = note;
  previous.date = current.date;
  list.push([note, 0]);
  /* Store it in the DOM */
  updateStave();
}

function deleteNote () {
  current = { note: -1, date: Date.now() };
  previous = { note: -1, date: Date.now() };
  list.pop();
  updateStave();
}

function listToCSV (list) {
  var csv = '';
  for (let i = 0; i < list.length; i++) {
    if (list[i][1] !== 0) {
      csv += list[i][0] + ',' + list[i][1] + '\n';
    }
  }
  return csv;
}

var codes = {
  KeyA: 60,
  KeyW: 61,
  KeyS: 62,
  KeyE: 63,
  KeyD: 64,
  KeyF: 65,
  KeyT: 66,
  KeyG: 67,
  KeyY: 68,
  KeyH: 69,
  KeyU: 70,
  KeyJ: 71,
  KeyK: 72,
  KeyO: 73,
  KeyL: 74,
  KeyP: 75,
  Semicolon: 76,
  Quote: 77
};

$(document).ready(function () {
  $('.keyboard').keydown(function (e) {
    /* Use codes since they are layout agnostic */
    if (codes[e.originalEvent.code] !== undefined) {
      onButton(codes[e.originalEvent.code]);
      e.preventDefault();
    } else if (e.originalEvent.code === 'Backspace') {
      deleteNote();
    }
  });

  $('.keyboard > a').click(function (e) {
    onButton($(e.currentTarget).data('midi'));
  });

  $('#searchCSV').click(function (e) {
    $.post('/api/uploadCSV', listToCSV(window.list), showResults);
  });
});

function showResults (results) {
  $('#results').text('');
  for (var i = 0; i < results.length; i++) {
    var reslink =
      "<a href='" +
      results[i]['path'] +
      "'>" +
      results[i]['title'] +
      '</a><br/>';
    $('#results').append(reslink);
  }
}

window.onload = function () {
  MIDI.loadPlugin({
    soundfontUrl: '/soundfont/',
    instrument: 'acoustic_grand_piano',
    onprogress: function (state, progress) {
      console.log(state, progress);
    },
    onsuccess: function () {
      // play the note
      MIDI.setVolume(0, 127);
    }
  });
};
