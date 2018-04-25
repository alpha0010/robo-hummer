import csv
from collections import deque
import urllib2

def readFeatures(audioFile):
    featureCSV = urllib2.urlopen("http://localhost:8080/analyze/" + audioFile)
    reader = csv.reader(featureCSV)

    features = []
    for row in reader:
        features.append([float(cell) for cell in row])

    return list(smooth(features))

def windowItr(iterable, window):
    buf = deque(maxlen=window)
    for val in iterable:
        buf.append(val)
        if len(buf) == window:
            yield list(buf)

def smooth(features):
    for segment in windowItr(features, 5):
        feature = []
        for col in range(len(segment[0])):
            feature.append(median([row[col] for row in segment]))
        yield feature

def median(data):
    data = sorted(data)
    n = len(data)
    if n % 2 == 1:
        return data[n // 2]
    else:
        i = n // 2
        return (data[i - 1] + data[i]) / 2
