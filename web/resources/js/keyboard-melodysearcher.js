/* global MIDI */
// TODO: Don't load MIDI into the global namespace.

/**
 * keyboard.js operates the keyboard using the interface.
 */

import $ from 'jquery';

export class MelodySearcher {
  constructor (rhBaseUrl, inputRenderer) {
    this.rhBaseUrl = rhBaseUrl;
    this.inputRenderer = inputRenderer;

    this.current = { note: -1, date: Date.now() };
    this.previous = { note: -1, date: Date.now() };
    this.list = [];
    this.inputSelector = '#staveInput';
    this.codes = {
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
      Quote: 77,
      Backspace: -1
    };
  }

  onButton (note) {
    if (note === -1) {
      return this.deleteNote();
    }
    this.current.note = note;
    this.current.date = Date.now(); // gives time in milliseconds since epoch.
    var length = this.current.date - this.previous.date;
    if (this.list.length > 0 && this.list[this.list.length - 1][1] === 0) {
      this.list[this.list.length - 1][1] = length;
    }

    if (this.previous.note !== -1) {
      MIDI.noteOff(0, this.previous.note, 0, 0);
    }
    MIDI.noteOn(0, this.current.note, 100, 0);

    this.previous.note = note;
    this.previous.date = this.current.date;
    this.list.push([note, 0]);
    this.inputRenderer.update(this.list, this.inputSelector);
  }

  deleteNote () {
    if (this.previous.note !== -1) {
      MIDI.noteOff(0, this.previous.note, 0, 0);
    }
    this.current = { note: -1, date: Date.now() };
    this.previous = { note: -1, date: Date.now() };
    this.list.pop();
    this.inputRenderer.update(this.list, this.inputSelector);
  }

  getCSV () {
    var csv = '';
    for (let i = 0; i < this.list.length; i++) {
      if (this.list[i][1] !== 0) {
        csv += `${this.list[i][0]},${this.list[i][1]}\n`;
      }
    }
    return csv;
  }

  bindToPage (id) {
    this.selector = '#' + id;
    this.createKeyboard();
  }

  createKeyboard () {
    var text = `<div class='keyboard'>
      <a href='#' data-midi='-1' class='Backspace'><span>&#x232B;</span></a>
      <a href='#' data-midi='60' class='WhiteKey'><span>A</span></a>
      <a href='#' data-midi='61' class='BlackKey cs'>W</a>
      <a href='#' data-midi='62' class='WhiteKey'><span>S</span></a>
      <a href='#' data-midi='63' class='BlackKey ds'>E</a>
      <a href='#' data-midi='64' class='WhiteKey'><span>D</span></a>
      <a href='#' data-midi='65' class='WhiteKey '><span>F</span></a>
      <a href='#' data-midi='66' class='BlackKey fs'>T</a>
      <a href='#' data-midi='67' class='WhiteKey'><span>G</span></a>
      <a href='#' data-midi='68' class='BlackKey gs'>Y</a>
      <a href='#' data-midi='69' class='WhiteKey'><span>H</span></a>
      <a href='#' data-midi='70' class='BlackKey as'>U</a>
      <a href='#' data-midi='71' class='WhiteKey'><span>J</span></a>
      <a href='#' data-midi='72' class='WhiteKey'><span>K</span></a>
      <a href='#' data-midi='73' class='BlackKey cs'>O</a>
      <a href='#' data-midi='74' class='WhiteKey'><span>L</span></a>
      <a href='#' data-midi='75' class='BlackKey ds'>P</a>
      <a href='#' data-midi='76' class='WhiteKey'><span>;</span></a>
      <a href='#' data-midi='77' class='WhiteKey'><span>'</span></a>
      <form><input type='text'></input></form>
      </div>
      <div id='staveInput'></div>
      <button id='searchCSV' class='btn btn-primary'>Search</button>
      <div id='results'></div>`;
    $(this.selector).html(text);

    $('.keyboard').keydown(e => {
      /* Use codes since they are layout agnostic */
      if (this.codes[e.originalEvent.code] !== undefined) {
        this.onButton(this.codes[e.originalEvent.code]);
        e.preventDefault();
      }
    });

    $('.keyboard > a[data-midi]').click(e => {
      this.onButton(parseInt($(e.currentTarget).data('midi')));
      e.preventDefault();
    });

    $('#searchCSV').click(e => {
      $.post(
        this.rhBaseUrl + '/api/uploadCSV',
        this.getCSV(),
        this.showResults
      );
    });
  }

  showResults (results) {
    $('#results').text('');
    for (var i = 0; i < results.length; i++) {
      var reslink = `<a href='${results[i]['path']}'>
        ${results[i]['title']}
      </a><br/>`;
      $('#results').append(reslink);
    }
  }
}
