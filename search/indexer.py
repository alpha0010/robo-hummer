#!/usr/bin/env python3
from datetime import timedelta
import json
from namedb import NameDB
import nmslib
import os
import searcher
import sys
import time
import xml.etree.ElementTree as ET

noteNumbers = {
    "C": 0,
    "D": 2,
    "E": 4,
    "F": 5,
    "G": 7,
    "A": 9,
    "B": 11
}


def musicXmlToNotes(fileName):
    """ Extract the sequence of notes from a musicXML file. """
    root = ET.parse(fileName).getroot()
    duration = 0
    for note in root.find("part").findall("measure/note"):
        # Skip rests.
        if note.find("rest") is not None:
            continue

        # Skip grace notes.
        if note.find("grace") is not None:
            continue

        step = note.find("pitch/step").text
        octave = int(note.find("pitch/octave").text)
        alter_q = note.find("pitch/alter")
        duration += int(note.find("duration").text)

        # Sanity check.
        if duration <= 0:
            continue

        alter = 0
        if alter_q is not None:
            alter = int(alter_q.text)

        midinote = 12 * (octave + 2) + noteNumbers[step] + alter

        # If the note starts a tie, it should continue.
        if note.find("tie[@type='start']") is None:
            yield {
                "freq": midinote,
                "len": duration
            }
            duration = 0


class ProgressBar:
    def __init__(self, count, width=40):
        self.step = 0
        self.count = count
        self.width = width
        self.lastRender = -1

    def start(self):
        self.startTime = time.time()
        sys.stdout.write("\n")
        self.renderBar()

    def advance(self):
        self.step = min(self.step + 1, self.count)
        if time.time() > self.lastRender + 1:
            # Render at most once per second.
            self.renderBar()

    def finish(self):
        self.step = self.count
        self.renderBar()
        sys.stdout.write("\n")

    def renderBar(self):
        percent = float(self.step) / self.count
        progress = int(round(self.width * percent))

        curTime = time.time()
        elapsedSeconds = curTime - self.startTime
        elapsed = timedelta(seconds=round(elapsedSeconds))
        estimated = "--"
        if elapsedSeconds > 5 and percent > 0.001:
            estimated = timedelta(
                seconds=round(elapsedSeconds / percent)
            )

        bar = "\r[{}{}] {}/{}".format(
            "-" * progress,
            " " * (self.width - progress),
            elapsed,
            estimated
        )

        sys.stdout.write(bar)
        sys.stdout.flush()
        self.lastRender = curTime


def main(argv):
    """ Create the search index. """

    indexpath = argv[1]

    # TODO: Is this context length optimal?
    contextLen = 4
    sqliteDbName = indexpath + "/file-index-new.sqlite"

    nameDB = NameDB(sqliteDbName)
    searchIndex = nmslib.init()

    # TODO: Consider reading stdin beforehand to know many files there will be.
    bar = ProgressBar(8000)
    bar.start()

    # Each filename is passed into stdin
    for fileName in sys.stdin:
        fileName = fileName.strip()
        if fileName.split('.', 1)[1] == 'musicxml':
            notes = musicXmlToNotes(fileName)
            features = list(searcher.extractAllFeatures(notes, contextLen))
        else:
            features = json.load(open(fileName))
        featureIDs = nameDB.generateIDs(nameDB[fileName], len(features))
        searchIndex.addDataPointBatch(data=features, ids=featureIDs)
        bar.advance()

    bar.finish()

    # TODO: Do we want any parameters?
    # https://github.com/searchivarius/nmslib/blob/master/similarity_search/src/method/hnsw.cc#L157
    searchIndex.createIndex()

    searchIndex.saveIndex(indexpath + "/notes.index")
    os.rename(sqliteDbName, indexpath + "/file-index.sqlite")

    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv))
