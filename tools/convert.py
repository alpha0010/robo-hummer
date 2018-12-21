#!/usr/bin/python3
from music21 import *

import sys
import os

filename = sys.argv[1]
outputformat = sys.argv[2]

if ( outputformat == 'harmony.musicxml' ):
	s = converter.parse(filename)
	s.write( 'xml', '/dev/stdout' )
elif( outputformat == 'harmony.midi' ):
	s = converter.parse(filename)
	# Creates a temporary file
	path = s.write( 'midi' )
	# Read file as bytes, write as buffer
	# so python's io.TextIOBase doesn't try to decode these bytes into ASCII.
	sys.stdout.buffer.write( open( path, 'rb' ).read() )
	os.remove( path )
elif( outputformat == 'incipit.json' ):
	import incipit
elif( outputformat == 'dynamic.svg' ):
	import dynamic
