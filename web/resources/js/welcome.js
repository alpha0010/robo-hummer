var MediaStreamRecorder = require('msr');

Welcome = {
  init: function () {
    Welcome.mediaRecorder = null;

    jQuery('#record').click(Welcome.onRecord);
    jQuery('#save').click(Welcome.onSave);
  },

  onRecord: function () {
    navigator.getUserMedia(
      { audio: true },
      Welcome.onMediaSuccess,
      Welcome.onMediaError
    );
  },

  onSave: function () {
    if (Welcome.mediaRecorder) {
      Welcome.mediaRecorder.stop();
    }
  },

  onMediaSuccess: function (stream) {
    var mediaRecorder = new MediaStreamRecorder(stream);
    mediaRecorder.mimeType = 'audio/wav';
    mediaRecorder.ondataavailable = Welcome.uploadFile;

    mediaRecorder.start(30 * 1000);
    Welcome.mediaRecorder = mediaRecorder;
  },

  onMediaError: function (e) {
    console.error('Media error', e);
  },

  uploadFile: function (blob) {
    var fd = new FormData();
    fd.append('audio', blob);

    jQuery
      .ajax({
        type: 'POST',
        url: '/api/upload',
        data: fd,
        processData: false,
        contentType: false
      })
      .done(function (data) {
        console.log('Post success');
        console.log(data);
      })
      .fail(function () {
        console.log('Post fail');
      });
  }
};

jQuery(document).ready(Welcome.init);
