/* globals MIDI */

import { VexFlowRenderer } from './keyboard-vexflow';
import { MelodySearcher } from './keyboard-melodysearcher';

window.onload = function () {
  MIDI.loadPlugin({
    soundfontUrl: '/soundfont/',
    instrument: 'acoustic_grand_piano',
    onprogress: function (state, progress) {
      console.log(state, progress);
    },
    onsuccess: function () {
      MIDI.setVolume(0, 127);
    }
  });
  var vfr = new VexFlowRenderer();
  var ms = new MelodySearcher('', vfr);
  ms.bindToPage('melodySearch');
};
