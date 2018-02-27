import csv
import logging
import tensorflow as tf
import urllib2

def main():
    # Train.
    data = [
        ("c4/021.ogg", 261.626),
        ("c4/022.ogg", 261.626),

        ("d4/021.ogg", 293.665),
        ("d4/022.ogg", 293.665),

        ("e4/021.ogg", 329.628),
        ("e4/022.ogg", 329.628),

        ("f4/021.ogg", 349.228),
        ("f4/022.ogg", 349.228),

        ("g4/021.ogg", 391.995),
        ("g4/022.ogg", 391.995),

        ("a4/021.ogg", 440.000),
        ("a4/022.ogg", 440.000),

        ("b4/021.ogg", 493.883),
        ("b4/022.ogg", 493.883),

        ("c5/021.ogg", 523.251),
        ("c5/022.ogg", 523.251),
    ]

    features = []
    freqs = []
    for datum in data:
        nFeats, nFreqs = readFeatures(*datum)
        features.extend(nFeats)
        freqs.extend(nFreqs)

    melFeatureColumn = tf.feature_column.numeric_column(
        key="mel",
        shape=len(features[0])
    )

    model = tf.estimator.DNNRegressor(
        hidden_units=[512, 256, 128], # Magic?
        feature_columns=[melFeatureColumn]
    )

    logging.getLogger().setLevel(logging.INFO)
    # Increase steps later for higher accuracy.
    model.train(input_fn=lambda:inputTrainFn(features, freqs), steps=5000)

    # Predict.
    nFeats, nFreqs = readFeatures("a4/020.ogg", 440.000)
    predictions = model.predict(input_fn=lambda:inputPredictFn(nFeats))
    for (pred, expect) in zip(predictions, nFreqs):
        print pred, expect

def inputTrainFn(features, freqs):
    dataset = tf.data.Dataset.from_tensor_slices((
        {"mel": tf.convert_to_tensor(features)},
        tf.constant(freqs)
    ))
    return dataset.shuffle(1000)   \
        .repeat()                  \
        .batch(1000)                \
        .make_one_shot_iterator()  \
        .get_next()

def inputPredictFn(features):
    return {"mel": tf.convert_to_tensor(features)}

def readFeatures(notes, freq):
    featureCSV = urllib2.urlopen("http://localhost:8080/analyze/data/notes/" + notes)
    reader = csv.reader(featureCSV)

    features = []
    freqs = []
    for row in reader:
        features.append([float(cell) for cell in row])
        freqs.append(freq)

    return (features, freqs)

if __name__ == "__main__":
    main()
