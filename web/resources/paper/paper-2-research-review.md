## Research Review

Searching using the on-screen keyboard on
[hymnary.org](https://hymnary.org/melody/search) is quite cumbersome.
Various companies already produce software capable of recognizing songs from
their actual recordings, however they necessitate a high degree of similarity;
the audio fragment must essentially be from the same recording for their audio
fingerprinting techniques to work.

We researched audio analysis libraries, and found Marsyas to be a good first step for
getting quantitative feature data from audio files.

We decided to use Tensorflow to train our neural network.
