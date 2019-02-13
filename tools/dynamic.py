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
colors = ['yellow', 'cyan', 'red', '#e500ff', '#ff5a00', '#00ff5b', '#005fff']
parts = {}
highPos = {}
lowPos = {}


def print(x):
    sys.stdout.buffer.write(x.encode('utf-8'))


def rectangle(x, y, w, h, lyrics, color):
    """Output an SVG group containing a rectangle and optionally including text for that rectangle.
        lyrics is a list containing the text for multiple lines.
            Additional lyrics will be stored as `data-vX` attributes, where X is an integer.
    """
    # Create "scaled" versions of each of the variables. The file will be rendered with data that allows
    # different parts to be scaled.
    sx = x * xScale
    sw = w * xScale
    sy = y * defaultYScale
    sh = h * defaultYScale
    style = "stroke-width: %i; stroke:rgb(0,0,0); opacity: 0.5;" \
            % (border)
    border2 = border * 2
    print("\n")
    if lyrics:
        print("<g>")
    print("<rect x='%i' y='%i' width='%i' height='%i'"
          % (sx, sy, sw, sh)
          + " data-x='%f' data-y='%f' data-width='%f' data-height='%f'"
          % (x, y, w, h)
          + " fill='%s'"
          % (color)
          + " style='%s'/>"
          % (style))
    if lyrics:
        dataVerses = ""
        for lyric in lyrics:
            escapedText = lyric.rawText
            escapedText = XMLescape(escapedText, {"'": "&apos;"})
            dataVerses += "data-v" + str(lyric.number) + "='" + escapedText + "' "
        lyricX = sx + border
        print("<text x='%i' data-textlength='%i' lengthAdjust='spacingAndGlyphs' "
              % (lyricX, sw)
              + "y='100%%' dy='%i' font-size='%ipt' %s>"
              % (defaultFontSize * (-1 / 3), defaultFontSize, dataVerses))

        text = XMLescape(lyrics[0].rawText)
        textBytes = text.encode('utf-8').strip()
        sys.stdout.buffer.write(textBytes)
        print("</text>")
        print("</g>")


def verticalLine(x):
    x = x * xScale
    print("<rect x='%i' y='0' width='%i' height='100%%'/>"
          % (x, border))


def trackLowHighPos(part, pitch):
    if part in lowPos:
        if pitch < lowPos[part]:
            lowPos[part] = pitch
    else:
        lowPos[part] = pitch
    if part in highPos:
        if pitch > highPos[part]:
            highPos[part] = pitch
    else:
        highPos[part] = pitch


def colorFromPart(part):
    if part in parts:
        return parts[part]
    parts[part] = colors.pop()
    return parts[part]


filename = sys.argv[1]
outputformat = sys.argv[2]


so = music21.converter.parse(filename)

s = so.voicesToParts()

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
print("<svg width='%i' height='%i'"
      % (songWidth, songHeight)
      + " data-songlength='%f' data-noterange='%i' %s>"
      % (songLength, noteRange, ns))
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

    if xLen != 0:
        for pitch in note.pitches:
            yPos = highNote - pitch.midi
            yLen = 1
            part = id(note.getContextByClass('Part'))
            color = colorFromPart(part)
            trackLowHighPos(part, yPos)

            lyrics = []
            if note.lyrics:
                lyrics = note.lyrics

            rectangle(xPos, yPos, xLen, yLen, lyrics, color)

print("<g id='measureBarLines'>")
for offset in measureOffsets.values():
    verticalLine(offset)
# Add a bar line at the end of the song.
verticalLine(songLength)
print("</g>")

print("<g id='parts'>")
i = -1
for key, value in parts.items():
    # Visually, these show the range of each part.
    # We set the x position as negative so it's not seen.
    rectangle(i, lowPos[key], 1, highPos[key] - lowPos[key] + 1, '', value)
    i = i - 1
print("</g>")

print("</svg>")
