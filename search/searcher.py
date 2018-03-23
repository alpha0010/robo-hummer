from collections import deque
from namedb import NameDB
import nmslib

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

# Test searching.
def main():
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

        print " ", nameDB.summarizeHits(featureIDs)

if __name__ == "__main__":
    main()
