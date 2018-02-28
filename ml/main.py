import csv
import logging
import tensorflow as tf
import urllib2

def main():
    model = train()
    #saver = tf.train.Saver()
    #with tf.Session() as sess:
    #tf.add_to_collection('vars', model)
    #sess = tf.Session()
    #saver.save( sess, "model.ckpt" )
    # saver.restore( sess, "model.ckpt" )

    predict(model, "c4/021.ogg", 60)
    predict(model, "d4/021.ogg", 62)
    predict(model, "e4/021.ogg", 64)
    predict(model, "f4/021.ogg", 65)
    predict(model, "cs4/flute.ogg", 65)
    predict(model, "g4/021.ogg", 67)
    predict(model, "a4/021.ogg", 69)
    predict(model, "b4/021.ogg", 71)
    predict(model, "c5/021.ogg", 72)
def train():
    # Train.
    data = [
        ("c4/021.ogg", 60),
        ("c4/022.ogg", 60),

        ("d4/021.ogg", 62),
        ("d4/022.ogg", 62),

        ("e4/021.ogg", 64),
        ("e4/022.ogg", 64),

        ("f4/021.ogg", 65),
        ("f4/022.ogg", 65),

        ("g4/021.ogg", 67),
        ("g4/022.ogg", 67),

        ("a4/021.ogg", 69.0),
        ("a4/022.ogg", 69.0),

        ("b4/021.ogg", 71),
        ("b4/022.ogg", 71),

        ("c5/021.ogg", 72),
        ("c5/022.ogg", 72),
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
        feature_columns=[melFeatureColumn],
        model_dir = '.'
    )

    logging.getLogger().setLevel(logging.INFO)
    # Increase steps later for higher accuracy.
    #model.train(input_fn=lambda:inputTrainFn(features, freqs), steps=5000)

    print("training done")
    return(model)

def predict(model, file, expected):
    # Predict.
    nFeats, nFreqs = readFeatures(file, expected)
    predictions = model.predict(input_fn=lambda:inputPredictFn(nFeats))
    sum = 0
    print file
    for (pred, expect) in zip(predictions, nFreqs):
        sum = sum + pred['predictions'][0]
        print pred['predictions'][0], expect
    print "average"
    print sum/len(nFreqs)

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
