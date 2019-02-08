#!/usr/bin/python3
import music21
import sys
from xml.sax.saxutils import escape as XMLescape

# How wide a quarter note is.
xScale = 84
# How tall a semitone is.
defaultYScale = 20
defaultFontSize = 18

border = 1
colors = ['red', 'green', 'blue', 'yellow', 'cyan', 'magenta']
parts = {}


def print(x):
    sys.stdout.buffer.write(x.encode('utf-8'))


def rectangle(x, y, w, h, lyrics, color, lyricLineY):
    """Output an SVG group containing a rectangle and optionally including text for that rectangle.
        lyrics is a list containing the text for multiple lines.
            Additional lyrics will be stored as `data-vX` attributes, where X is an integer.
        lyricLineY is an alternate Y value placement for the lyrics rather than on the rectangle.
            `data-y` is the Y value for being on the rectangle, `data-y-bottom` is the alternate placement.
    """
    # Create "scaled" versions of each of the variables. The file will be rendered with data that allows
    # different parts to be scaled.
    sx = x * xScale
    sw = w * xScale
    sy = y * defaultYScale
    sh = h * defaultYScale
    style = "fill:%s; stroke-width: %i; stroke:rgb(0,0,0); opacity: 0.5;" \
            % (color, border)
    border2 = border * 2
    print("\n")
    print("<g>")
    print("<rect x='%i' y='%i' width='%i' height='%i'"
          % (sx, sy, sw, sh) +
          " data-x='%f' data-y='%f' data-width='%f' data-height='%f'"
          % (x, y, w, h) +
          " style='%s'/>"
          % (style)
    )
    if lyrics:
        dataVerses = ""
        for lyric in lyrics:
            escapedText = lyric.rawText
            escapedText = XMLescape(escapedText, {"'": "&apos;"})
            dataVerses += "data-v" + str(lyric.number) + "='" + escapedText + "' "
        lyricX = sx + border
        lyricY = sy + sh - border
        print("<text x='%i' data-textlength='%i' lengthAdjust='spacingAndGlyphs' "
              % (lyricX, sw) +
              "y='%i' data-y='%i' data-y-bottom='%i' font-size='%ipt' %s>"
              % (lyricY, lyricY, lyricLineY, defaultFontSize, dataVerses))

        # TODO: use syllabic for something.
        text = XMLescape(lyrics[0].rawText)
        textBytes = text.encode('utf-8').strip()
        sys.stdout.buffer.write(textBytes)
        print("</text>")
    print("</g>")


def verticalLine(x):
    x = x * xScale
    print("<rect x='%i' y='0' width='%i' height='100%%'/>"
          % (x, border))


def colorFromPart(part):
    if part in parts:
        return parts[part]
    parts[part] = colors.pop()
    return parts[part]


filename = sys.argv[1]
outputformat = sys.argv[2]


s = music21.converter.parse(filename)

# Get the length of the song
songLength = s.duration.quarterLength

# Get the range of the notes
lowNote = min(s.pitches).midi
highNote = max(s.pitches).midi
noteRange = highNote - lowNote + 1

songWidth = songLength * xScale
songHeight = (noteRange * defaultYScale) + (1.5 * defaultFontSize)

measureLengths = {0: 0}
measureOffsets = {0: 0}

# Output notes in place
print("<?xml version='1.0' encoding='utf-8'?>")
ns = 'xmlns="http://www.w3.org/2000/svg"'
print("<svg width='%i' height='%i' %s>" % (songWidth, songHeight, ns))
for note in s.recurse().notes:
    if hasattr(note, 'midiTickStart'):
        xPos = note.midiTickStart / 1024
    else:
        # master.musicxml ensures that the measure numbers
        # are sequential and distinct integers.
        measureNum = int(note.measureNumber)
        beatsThisMeasure = note.getContextByClass("Measure").duration.quarterLength
        measureLengths[measureNum] = beatsThisMeasure
        measureOffsets[measureNum] = measureOffsets[measureNum - 1] + measureLengths[measureNum - 1]
        xPos = measureOffsets[measureNum] + ((note.beat - 1) * note.beatDuration.quarterLength)

    xLen = note.duration.quarterLength

    for pitch in note.pitches:
        yPos = highNote - pitch.midi
        yLen = 1
        # TODO: Consider using music_tokens.partify
        color = colorFromPart(note.getContextByClass('Part').recurse().getElementsByClass('Instrument')[0])

        lyrics = []
        if note.lyrics:
            lyrics = note.lyrics

        lyricLineY = (noteRange + 1) * defaultYScale
        rectangle(xPos, yPos, xLen, yLen, lyrics, color, lyricLineY)

print("<g id='measureBarLines'>")
for offset in measureOffsets.values():
    verticalLine(offset)
# Add a bar line at the end of the song.
verticalLine(songLength)
print("</g>")

print("</svg>")

# Output lyrics below the notes
