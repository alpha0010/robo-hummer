#!/usr/bin/python3
from music21 import *

import sys

filename = sys.argv[1]
outputformat = sys.argv[2]

if ( outputformat == 'harmony.musicxml' ):
	s = converter.parse(filename)
	s.write( 'xml', '/dev/stdout' )
elif( outputformat == 'incipit.json' ):
	import incipit
