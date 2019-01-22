#!/usr/bin/python3
import json
import sys
import xml.etree.ElementTree as ElementTree

filename = sys.argv[1]

tree = ElementTree.parse(filename)
root = tree.getroot()
group = root.find("{http://www.w3.org/2000/svg}g[@id='measureOffsets']")

measureOffsets = []

for line in list(group):
    measureOffsets.append(int(line.attrib['x']))

print(json.dumps({'measureOffsets': measureOffsets}))
