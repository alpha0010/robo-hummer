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
    for (pred, feat) in zip(predictions, features):
        print pred["predictions"][0]

    return 0

def inputPredictFn(features):
    return {"mel": tf.convert_to_tensor(features)}

if __name__ == "__main__":
    sys.exit(main(sys.argv))
