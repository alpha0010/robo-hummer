import api
import sys
import tensorflow as tf

# Predict the sequence of MIDI notes in an audio file.
def main(argv):
    features = api.readFeatures(argv[1])

    # Initialize machine learning model.
    melFeatureColumn = tf.feature_column.numeric_column(
        key="mel",
        shape=len(features[0])
    )
    model = tf.estimator.DNNRegressor(
        hidden_units=[16, 16],
        feature_columns=[melFeatureColumn],
        model_dir="model"
    )

    predictions = model.predict(input_fn=lambda:inputPredictFn(features))
    notes = []
    for (pred, feat) in zip(predictions, features):
        notes.append(pred["predictions"][0])

    # Process raw results.
    smoothed = smooth(notes)
    chunks = segment(smoothed)

    #print api.median([len(chunk) for chunk in chunks])
    #print api.median([item["len"] for item in coalesce(chunks)])
    for item in coalesce(chunks):
        print item

    return 0

# Tensorflow data helper.
def inputPredictFn(features):
    return {"mel": tf.convert_to_tensor(features)}

# Temporally normalize a list.
#
# Uses median of 3.
def smooth(notes):
    for segment in api.windowItr(notes, 3):
        yield api.median(segment)

# Chunk a list into sublists of similar values.
#
# TODO: Test different magic numbers.
def segment(notes):
    currVal = None
    runningAvg = None
    segmentBuf = []
    for note in notes:
        if currVal is None:
            currVal = note
            runningAvg = note
        runningAvg = (2 * runningAvg + note) / 3
        if abs(currVal - runningAvg) > 0.8 and len(segmentBuf) > 1:
            yield segmentBuf
            # TODO: Consider setting to: api.median(segmentBuf)
            currVal = note
            segmentBuf = []
        segmentBuf.append(note)

    if segmentBuf:
        yield segmentBuf

# Convert a list of chunks into notes.
#
# TODO: Consider combining chunks when their length is significanly shorter
#       than the length of the longer chunks.
def coalesce(chunks):
    currData = []
    for chunk in chunks:
        if currData and abs(api.median(currData) - api.median(chunk)) > 0.8:
            yield {
                "freq": api.median(currData),
                "len": len(currData)
            }
            currData = []
        currData += chunk

    if currData:
        yield {
            "freq": api.median(currData),
            "len": len(currData)
        }

if __name__ == "__main__":
    sys.exit(main(sys.argv))
