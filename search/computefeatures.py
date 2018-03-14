import csv
from collections import deque
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

def main():
    notesCSV = urllib2.urlopen("http://localhost:8080/midi/data/scale-c-major/001.ogg")
    reader = csv.reader(notesCSV)
    notes = []
    for row in reader:
        freq = float(row[2])
        if not notes or notes[-1]["freq"] != freq:
            notes.append({
                "freq": freq,
                "len": 1
            })
        else:
            notes[-1]["len"] += 1
    print notes
    print

    for segment in windowItr(notes, 4):
        print segment
        print computeFeatures(segment)
        print

if __name__ == "__main__":
    main()
