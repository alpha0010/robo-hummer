Welcome = {
    init: function() {
        Welcome.mediaRecorder = null;

        jQuery("#record").click(Welcome.onRecord);
        jQuery("#save").click(Welcome.onSave);
    },

    onRecord: function() {
        navigator.mediaDevices.
            getUserMedia({audio: true}).
            then(Welcome.onMediaSuccess).
            catch(Welcome.onMediaError);
    },

    onSave: function() {
        if (Welcome.mediaRecorder) {
            Welcome.mediaRecorder.stop();
        }
    },

    onMediaSuccess: function(stream) {
        var mediaRecorder = new MediaStreamRecorder(stream);
        mediaRecorder.ondataavailable = Welcome.uploadFile;

        mediaRecorder.start(30 * 1000);
        Welcome.mediaRecorder = mediaRecorder;
    },

    onMediaError: function(e) {
        console.error("Media error", e);
    },

    uploadFile: function(blob) {
        var ext = blob.type.substring(blob.type.lastIndexOf("/") + 1);
        var fd = new FormData();
        fd.append("fname", "the-sound." + ext);
        fd.append("data", blob);

        jQuery.ajax({
            type: "POST",
            url: "/api/upload",
            data: fd,
            processData: false,
            contentType: false
        }).done(function(data) {
            console.log("Post success");
            console.log(data);
        }).fail(function() {
            console.log("Post fail");
        });
    }
};

jQuery(document).ready(Welcome.init);
