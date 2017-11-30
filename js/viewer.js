Viewer = {
    init: function() {
        var graph = document.getElementById("graph");
        graph.width = graph.clientWidth;
        graph.height = graph.width * 9 / 16;
        Viewer.graph = graph;

        Viewer.rate = 700;

        jQuery.ajax({
            url: "data/notes/a4/001.json",
            dataType: "json",
            /*
            beforeSend: function(x) {
                // Needed if reading off local disk.
                x.overrideMimeType("application/json;charset=UTF-8");
            },
            */
            success: function(data) {
                Viewer.data = data;
                Viewer.frame = 0;
                window.setTimeout(Viewer.render, Viewer.rate);
            }
        });
    },

    render: function() {
        var row = Viewer.data.data[Viewer.frame];
        var dc = Viewer.graph.getContext("2d");
        var width = Viewer.graph.width;
        var height = Viewer.graph.height;
        var dataMin = Viewer.data.min;
        var dataMax = Viewer.data.max;

        var widthScale = width / row.length;
        var heightScale = height / (dataMax - dataMin);

        dc.clearRect(0, 0, width, height);

        dc.beginPath();
        dc.strokeStyle = "black";
        dc.moveTo(
            0,
            height - (row[0] - dataMin) * heightScale
        );
        for (var i = 1; i < row.length; ++i) {
            dc.lineTo(
                i * widthScale,
                height - (row[i] - dataMin) * heightScale
            );
        }
        dc.stroke();

        ++Viewer.frame;
        if (Viewer.frame < Viewer.data.data.length)
        {
            window.setTimeout(Viewer.render, Viewer.rate);
        }
    }
};

jQuery(document).ready(Viewer.init);
