from collections import deque
import itertools
from namedb import NameDB
import nmslib
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

# Generate lists of size 'window' starting at each value of 'iterable'.
def windowItr(iterable, window):
    buf = deque(maxlen=window)
    for val in iterable:
        buf.append(val)
        if len(buf) == window:
            yield list(buf)

# Compute relative changes in frequency and length, as compared to the first
# element.
def computeFeatures(segment):
    features = []

    refFreq = segment[0]["freq"]
    refLen = float(segment[0]["len"])
    for val in segment[1:]:
        features.append(val["freq"] - refFreq)
        features.append(val["len"] / refLen)

    return features

# Extract all feature points from the notes.
def extractAllFeatures(notes, contextLen):
    for segment in windowItr(notes, contextLen):
        yield computeFeatures(segment)

# Test search index creation.
def buildSearchIndex():
    contextLen = 4

    # TODO: Before updating the search index, it would be wise to clear the
    #       file index (or at least, the `featureIDs` table) to avoid
    #       accumulating unused data.
    nameDB = NameDB("file-index.sqlite")
    searchIndex = nmslib.init()

    files = ["data/AAHH2001-120.xml", "data/AAHH2001-136.xml"]

    for fileName in files:
        notes = musicXmlToNotes(fileName)
        features = list(extractAllFeatures(notes, contextLen))
        featureIDs = nameDB.generateIDs(nameDB[fileName], len(features))
        searchIndex.addDataPointBatch(data=features, ids=featureIDs)

    # TODO: Do we want any parameters?
    # https://github.com/searchivarius/nmslib/blob/master/similarity_search/src/method/hnsw.cc#L157
    searchIndex.createIndex()

    searchIndex.saveIndex("notes.index")

# Test searching.
def search():
    contextLen = 4

    nameDB = NameDB("file-index.sqlite")
    searchIndex = nmslib.init()
    searchIndex.loadIndex("notes.index")

    testData = [
        ("data/AAHH2001-120.xml", [{"freq": 81, "len": 6720}, {"freq": 79, "len": 6720}, {"freq": 79, "len": 6720}, {"freq": 81, "len": 6720}, {"freq": 83, "len": 6720}, {"freq": 83, "len": 10080}, {"freq": 81, "len": 3360}]),
        ("data/AAHH2001-136.xml", [{"freq": 77, "len": 6720}, {"freq": 73, "len": 3360}, {"freq": 75, "len": 5040}, {"freq": 75, "len": 1680}, {"freq": 75, "len": 3360}, {"freq": 75, "len": 3360}, {"freq": 73, "len": 3360}])
    ]

    for fileName, notes in testData:
        print "Expected:", fileName

        features = list(extractAllFeatures(notes, contextLen))
        results = searchIndex.knnQueryBatch(queries=features, k=5)

        featureIDs = []
        for IDs, diffs in results:
            featureIDs += list(IDs)

        print nameDB.summarizeHits(featureIDs)

def main():
    buildSearchIndex()
    search()

if __name__ == "__main__":
    main()
