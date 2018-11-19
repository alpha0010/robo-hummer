#!/usr/bin/python3
from music21 import *

import sys

filename = sys.argv[1]
outputformat = sys.argv[2]

if ( outputformat == 'harmony.musicxml' ):
	s = converter.parse(filename)
	s.write( 'xml', '/dev/stdout' )
elif( outputformat == 'harmony.midi' ):
	s = converter.parse(filename)
	s.write( 'midi', '/tmp/file.midi' )
	import sys
	# Read file as bytes, write as buffer
	# so python's io.TextIOBase doesn't try to decode these bytes into ASCII.
	sys.stdout.buffer.write( open( '/tmp/file.midi', 'rb' ).read() )
elif( outputformat == 'incipit.json' ):
	import incipit
