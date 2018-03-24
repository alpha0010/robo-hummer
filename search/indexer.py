from datetime import timedelta
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

# Extract the sequence of notes from a music XML file.
def musicXmlToNotes(fileName):
    root = ET.parse(fileName).getroot()
    for note in root.findall("part[@id='P1']/measure/note"):
        # Skip rests.
        if note.find("rest") is not None:
            continue

        # Skip grace notes.
        if note.find("grace") is not None:
            continue

        step     = note.find("pitch/step").text
        octave   = int(note.find("pitch/octave").text)
        alter_q  = note.find("pitch/alter")
        duration = int(note.find("duration").text)

        # Sanity check.
        if duration <= 0:
            continue

        alter = 0
        if alter_q is not None:
            alter = int(alter_q.text)

        midinote = 12 * (octave + 2) + noteNumbers[step] + alter

        yield {
            "freq": midinote,
            "len":  duration
        }

class ProgressBar:
    def __init__(self, count, width = 40):
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

# Create the search index.
def main(argv):
    if len(argv) < 2:
        sys.stderr.write("Usage: %s music-xml-files...\n" % (argv[0],))
        return 1

    contextLen = 4
    sqliteDbName = "file-index.sqlite"

    # Clear the file-to-id database.
    try:
        os.remove(sqliteDbName)
    except OSError:
        pass

    nameDB = NameDB(sqliteDbName)
    searchIndex = nmslib.init()

    files = argv[1:]

    bar = ProgressBar(len(files))
    bar.start()

    for fileName in files:
        notes = musicXmlToNotes(fileName)
        features = list(searcher.extractAllFeatures(notes, contextLen))
        featureIDs = nameDB.generateIDs(nameDB[fileName], len(features))
        searchIndex.addDataPointBatch(data=features, ids=featureIDs)
        bar.advance()

    bar.finish()

    # TODO: Do we want any parameters?
    # https://github.com/searchivarius/nmslib/blob/master/similarity_search/src/method/hnsw.cc#L157
    searchIndex.createIndex()

    searchIndex.saveIndex("notes.index")

    return 0

if __name__ == "__main__":
    sys.exit(main(sys.argv))
