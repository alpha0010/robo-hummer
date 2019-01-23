#!/usr/bin/python3

import xml.etree.ElementTree as ElementTree


def makeMasterMusicXML(path):
    tree = ElementTree.parse(path)
    root = tree.getroot()
    for p in root.findall('part'):
        # Renumber the measures to be sequential with distinct numbers.
        i = 1
        for m in p.findall('measure'):
            m.attrib['number'] = str(i)
            i = i + 1
    tree.write(path, xml_declaration=True, encoding='utf-8', method='xml')
