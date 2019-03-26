#!/usr/bin/env python3
import csv
from collections import deque
import json
import math
from namedb import NameDB
import nmslib
import sys
import urllib


def windowItr(iterable, window):
    """ Generate lists of size 'window' starting at each value of 'iterable'. """
    buf = deque(maxlen=window)
    for val in iterable:
        buf.append(val)
        if len(buf) == window:
            yield list(buf)


def computeFeatures(segment):
    """ Compute relative changes in frequency and length, as compared to the first
        element.
    """
    features = []

    refFreq = segment[0]["freq"]
    refLen = float(segment[0]["len"])
    for val in segment[1:]:
        features.append(val["freq"] - refFreq)
        relativeLength = val["len"] / refLen
        # Testing about 26000 media files,
        # 95% of the pitch differences were between -9 and 8 semitones.
        # 95% of the relative lengths were between 0 and 4.
        # The following transformation brings the relative length into
        # a similar domain as the pitch offset,
        # so that nmslib distances are more meaningful.
        logBase = 4
        multiplier = 8
        features.append(math.log(relativeLength, logBase) * multiplier)

    return features


def extractAllFeatures(notes, contextLen):
    """ Extract all feature points from the notes. """
    for segment in windowItr(notes, contextLen):
        yield computeFeatures(segment)


def main(argv):
    """ Search. """
    contextLen = 4

    indexpath = argv[1]

    # Load the search index.
    nameDB = NameDB(indexpath + "/file-index.sqlite")
    searchIndex = nmslib.init(space='l2')
    searchIndex.loadIndex(indexpath + "/notes.index")

    notes = []

    if argv[2] == '--csv':
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
    k = 30
    qFeatures = list(extractAllFeatures(notes, contextLen))
    results = searchIndex.knnQueryBatch(queries=qFeatures, k=k)

    # Store information that will be used to summarize results.
    threeples = []

    # There are (up to) k results for each of the query features.
    for i in range(0, len(qFeatures)):
        IDs = results[i][0]
        distances = results[i][1]
        for j in range(0, len(IDs)):
            threeples.append((IDs[j], distances[j], i))

    print(json.dumps(
        nameDB.summarizeHits(threeples)[:10],
        indent=4,
        sort_keys=True
    ))

    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv))
