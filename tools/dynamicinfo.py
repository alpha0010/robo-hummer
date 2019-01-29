#!/usr/bin/python3
import json
import sys
import xml.etree.ElementTree as ElementTree

filename = sys.argv[1]

tree = ElementTree.parse(filename)
root = tree.getroot()
group = root.find("{http://www.w3.org/2000/svg}g[@id='measureBarLines']")

barLines = []

for line in list(group):
    barLines.append(int(line.attrib['x']))

width = int(root.attrib['width'])

print(json.dumps({'measureBarLines': barLines, 'width': width}))
