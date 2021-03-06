import api
import glob
import logging
import os
import sys
import tensorflow as tf

# Train a neural network to guess notes from an audio file.
def main(argv):
    data = listData(argv[1])

    # Pull features from analyzer server.
    features = []
    freqs = []
    for datum in data:
        nFeats, nFreqs = readFeatures(*datum)
        features.extend(nFeats)
        freqs.extend(nFreqs)

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

    # Train.
    logging.getLogger().setLevel(logging.INFO)
    model.train(
        input_fn=lambda:inputTrainFn(features, freqs),
        steps=int(60 * 60 * 24 / 0.004346347821159404)
    )

    return 0

# Enumerate the dataset.
def listData(searchPath):
    data = []
    searchPath = searchPath.rstrip("/")

    nameToMidi = genNoteToMidi()
    for note in os.listdir(searchPath):
        folder = searchPath + "/" + note
        for sample in glob.glob(folder + "/*.wav"):
            sample = os.path.relpath(sample, searchPath)
            data.append((sample, float(nameToMidi[note])))

    return data

# Map note names to midi values.
def genNoteToMidi():
    names = [
        ("c", 2),
        ("d", 2),
        ("e", 1),
        ("f", 2),
        ("g", 2),
        ("a", 2),
        ("b", 1),
    ]

    nameToMidi = {}

    curOctave = 2
    curMidi = 36
    for x in range(5):
        for name, hop in names:
            nameToMidi[name + str(curOctave)] = curMidi
            curMidi += hop
        curOctave += 1

    return nameToMidi

# Tensorflow data helper.
def inputTrainFn(features, freqs):
    # TODO: Test different values.
    # These (may) impact performance and/or learning accuracy.
    bufferSize = 8000
    batchSize  = 2000

    dataset = tf.data.Dataset.from_tensor_slices((
        {"mel": tf.convert_to_tensor(features)},
        tf.constant(freqs)
    ))
    return dataset.shuffle(bufferSize)  \
        .repeat()                       \
        .batch(batchSize)               \
        .make_one_shot_iterator()       \
        .get_next()

# Pull feature vectors.
def readFeatures(notes, freq):
    print("Reading {}".format(notes))
    features = api.readFeatures("training/" + notes)
    return (features, [freq] * len(features))

if __name__ == "__main__":
    sys.exit(main(sys.argv))
