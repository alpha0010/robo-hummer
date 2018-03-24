from namedb import NameDB
import nmslib
import os
import searcher
import sys
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

        step     = note.find("pitch/step").text
        octave   = note.find("pitch/octave").text
        alter_q  = note.find("pitch/alter")
        duration = note.find("duration").text

        alter = "0"
        if alter_q is not None:
            alter = alter_q.text

        midinote = 12 * (int(octave) + 2) + int(noteNumbers[step]) + int(alter)

        yield {
            "freq": midinote,
            "len":  int(duration)
        }

class ProgressBar:
    def __init__(self, count, width = 60):
        self.step = 0
        self.stepSize = float(width) / count
        self.width = width

    def start(self):
        sys.stdout.write("[%s]" % (" " * self.width))
        sys.stdout.flush()
        sys.stdout.write("\b" * (self.width + 1))

    def advance(self):
        diff = min(
                   int(self.step + self.stepSize),
                   self.width
               ) - int(self.step)
        if diff > 0:
            sys.stdout.write("-" * diff)
            sys.stdout.flush()

        self.step += self.stepSize

    def finish(self):
        diff = self.width - int(self.step)
        if diff > 0:
            sys.stdout.write("-" * diff)
        sys.stdout.write("\n")

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
