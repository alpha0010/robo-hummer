import api
import sys
import tensorflow as tf

def main(argv):
    features = api.readFeatures(argv[1])

    melFeatureColumn = tf.feature_column.numeric_column(
        key="mel",
        shape=len(features[0])
    )
    model = tf.estimator.DNNRegressor(
        hidden_units=[64, 32],
        feature_columns=[melFeatureColumn],
        model_dir="model"
    )

    predictions = model.predict(input_fn=lambda:inputPredictFn(features))
    notes = []
    for (pred, feat) in zip(predictions, features):
        notes.append(pred["predictions"][0])

    smoothed = smooth(notes)
    chunks = segment(smoothed)

    #print api.median([len(chunk) for chunk in chunks])
    #print api.median([item["len"] for item in coalesce(chunks)])
    for item in coalesce(chunks):
        print item

    return 0

def inputPredictFn(features):
    return {"mel": tf.convert_to_tensor(features)}

def smooth(notes):
    for segment in api.windowItr(notes, 3):
        yield api.median(segment)

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
            currVal = note
            segmentBuf = []
        segmentBuf.append(note)

    if segmentBuf:
        yield segmentBuf

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
