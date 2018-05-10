### Design

We designed a pipeline to bring audio from a web interface through our machine learning,
and into the k-nearest neighbors search.

We collected audio samples to train the neural network.

In testing this, we discovered it to be unreliable,
so we created a keyboard interface to make sure the searching works properly.

Our search algorithm takes in the music XML corpus and divides it up into a
shifting window of the context length (we selected 4 notes). Each segment is
converted to a feature vector comprised of the pitches and lengths of each note
relative to the first note in the segment. The relative format allows us to
ignore differences in key and tempo.

The feature vectors are stored in an index for k-nearest neighbor queries. We
selected the the HNSW approximate k-NN algorithm for its superior performance
and accuracy.

At search time, the input music phrase is similarly divided into feature
vectors. These vectors are run through k-NN. The resulting points are grouped
by which song they are from, with the frequency becoming the match score.
