#!/usr/bin/python3
from music21 import *

import sys
import os
from masterMusicXML import makeMasterMusicXML

filename = sys.argv[1]
outputformat = sys.argv[2]

if (outputformat == 'harmony.musicxml') or (outputformat == 'master.musicxml'):
    s = converter.parse(filename)
    path = s.write('xml')
    if (outputformat == 'master.musicxml'):
        # Note: It's important that this XML file is trusted.
        # The XML parser in makeMasterMusicXML is not secure against
        # maliciously constructed data.
        makeMasterMusicXML(path)
    sys.stdout.buffer.write(open(path, 'rb').read())
    os.remove(path)
elif(outputformat == 'harmony.midi'):
    s = converter.parse(filename)
    # Creates a temporary file
    path = s.write('midi')
    # Read file as bytes, write as buffer
    # so python's io.TextIOBase doesn't try to decode these bytes into ASCII.
    sys.stdout.buffer.write(open(path, 'rb').read())
    os.remove(path)
elif(outputformat == 'incipit.json'):
    import incipit
elif(outputformat == 'dynamic.svg'):
    import dynamic
