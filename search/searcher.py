import csv
from collections import deque
import json
from namedb import NameDB
import nmslib
import sys
import urllib2

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

# Search.
def main(argv):
    contextLen = 4

    # Load the search index.
    nameDB = NameDB("file-index.sqlite")
    searchIndex = nmslib.init()
    searchIndex.loadIndex("notes.index")

    notes = []

    if argv[1] == '--csv':
        # Input is a list of notes.
        reader = csv.reader(sys.stdin)
        for row in reader:
            notes.append({
                "freq": float(row[0]),
                "len": float(row[1])
            })
    else:
        # Analyze for notes.
        notesCSV = urllib2.urlopen("http://localhost:8080/midi/" + argv[1])
        reader = csv.reader(notesCSV)
        for row in reader:
            freq = float(row[2])
            if not notes or notes[-1]["freq"] != freq:
                notes.append({
                    "freq": freq,
                    "len": 1
                })
            else:
                notes[-1]["len"] += 1

    # Search.
    #
    # TODO: 30 nearest-neighbors may or may not be optimal. This might also
    #       depend on index size.
    features = list(extractAllFeatures(notes, contextLen))
    results = searchIndex.knnQueryBatch(queries=features, k=30)

    # Process results.
    featureIDs = []
    for IDs, diffs in results:
        featureIDs += list(IDs)

    print json.dumps(
        nameDB.summarizeHits(featureIDs)[:10],
        indent=4,
        sort_keys=True
    )

    return 0

if __name__ == "__main__":
    sys.exit(main(sys.argv))
