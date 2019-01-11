#!/usr/bin/python3
import music21
import sys
from xml.sax.saxutils import escape as XMLescape

# How wide a quarter note is.
xScale = 75
# How tall a semitone is.
yScale = 20

border = 1
colors = [ 'red', 'green', 'blue', 'yellow', 'cyan', 'magenta' ]
parts = {}

def print( x ):
    sys.stdout.buffer.write( x.encode('utf-8') )

def rectangle(x,y,w,h,textBytes,color):
    x = x * xScale
    w = w * xScale
    y = y * yScale
    h = h * yScale
    style = "fill:%s; stroke-width: %i; stroke:rgb(0,0,0); opacity: 0.5;" % (color,border)
    border2 = border * 2
    #text = XMLescape( text )
    print( "<g>" )
    print( "<rect x='%i' y='%i' width='%i' height='%i' style='%s'/>" % (x,y,w,h, style) )
    print( "<text x='%i' y='%i'      font-size='%ipt'>"
        % (x+border,     y+h-border, h-border2) )
    if textBytes:
        sys.stdout.buffer.write( textBytes )
    print( "</text>" )
    print( "</g>" )

def colorFromPart( part ):
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
highNote  = max(s.pitches).midi
noteRange = highNote - lowNote + 1

songWidth = songLength * xScale
songHeight = noteRange * yScale

measureLengths = {0: 0}
measureOffsets = {0: 0}

# Output notes in place
print("<?xml version='1.0' encoding='utf-8'?>")
ns='xmlns="http://www.w3.org/2000/svg"'
print( "<svg width='%i' height='%i' %s>" % (songWidth, songHeight, ns) )
for note in s.recurse().notes:
        if hasattr(note, 'midiTickStart'):
            xPos = note.midiTickStart/1024
        else:
            # master.musicxml ensures that the measure numbers are sequential and distinct integers.
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
            color = colorFromPart( note.getContextByClass('Part').recurse().getElementsByClass('Instrument')[0] )

            string = False
            if note.lyrics:
                # TODO: use syllabic for something.
                string = note.lyrics[0].rawText
            if string:
                string = XMLescape(string)
                string = string.encode('utf-8').strip()

            rectangle( xPos, yPos, xLen, yLen, string, color)
print( "</svg>" )

# Output lyrics below the notes


