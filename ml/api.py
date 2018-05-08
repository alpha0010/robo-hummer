import csv
from collections import deque
try:
    from urllib2 import urlopen
except ImportError:
    # Python 3 support.
    from codecs import iterdecode
    from urllib.request import urlopen as urlopen3
    def urlopen(url):
        return iterdecode(urlopen3(url), "utf-8")

# Retrieve feature vectors of the audio file from the analyzer server.
def readFeatures(audioFile):
    featureCSV = urlopen("http://localhost:8080/analyze/" + audioFile)
    reader = csv.reader(featureCSV)

    features = []
    for row in reader:
        features.append([float(cell) for cell in row])

    return list(smooth(features))

# Split a list into sublists via a sliding window.
#
# window = 3
# [1, 2, 3, 4, 5]
# ->
# [[1, 2, 3,], [2, 3, 4], [3, 4, 5]]
def windowItr(iterable, window):
    buf = deque(maxlen=window)
    for val in iterable:
        buf.append(val)
        if len(buf) == window:
            yield list(buf)

# Temporally smooth a list of feature vectors.
#
# Uses median of 5.
def smooth(features):
    for segment in windowItr(features, 5):
        feature = []
        for col in range(len(segment[0])):
            feature.append(median([row[col] for row in segment]))
        yield feature

# Compute the median of a list.
def median(data):
    data = sorted(data)
    n = len(data)
    if n % 2 == 1:
        return data[n // 2]
    else:
        i = n // 2
        return (data[i - 1] + data[i]) / 2
