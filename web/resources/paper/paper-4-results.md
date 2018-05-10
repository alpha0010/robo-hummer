### Results

Our initial neural networks for predicting notes from audio data supported
excessive learning capabilities. This resulted in the networks learning the
exact training files. When fed new data, prediction performance was abysmal,
often ranging +/- 1 octave.

To improve the model, we reduced the size of the neural network while
increasing the number of input features. The prediction range narrowed to
approximately +/- 3 tones. A significant improvement, but insufficient accuracy
to make search via humming practical.

Our alternate method of predicting notes was based on the YIN algorithm.
Although this algorithm is commonly used in tuners, we were unable to get
useful results from it for hummed input. (Input from musical instruments tended
to be highly accurate, but was outside of the scope of our project.)
Pitch detection algorithms may warrant further investigation.

The second component of our system, search, yielded more successful results.
Even with mild inaccuracies in pitch and timing, our nearest-neighbor based
scoring algorithm generally ranked the correct tune as the top, or close to the
top result. Incorporating note length (in addition to just pitch) enabled our
relatively short context length of four notes to remain a useful distinguishing
factor.

However, the search was designed with audio input in mind. While humming,
getting note length correct is easier than with our fallback keyboard input.
As such, we may be able to provide more stable results by placing a lesser
weight on the note length components of the score. Also, our search algorithm
has increased difficulty in handling insertion and deletion mistakes. It
requires sufficient input to take search contexts from around, but not
including, this type of error. To make search more tolerant, we could include
in both our index and search contexts, all combinations of single-deletion
mistakes. This requires evaluation to prevent accurate searches from degrading.
