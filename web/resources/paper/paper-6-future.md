### Future Work

The neural net could be trained more to recognize humming.

We could experiment with LSTM - Long Short-Term Memory neural networks.

The new search with the
keyboard interface could possibly be used as a replacement for the old Hymnary
melodic search feature.
More improvements to the interface - such as editing the sample,
may be wanted.
Ability to import music from MIDI files is a critical feature for this to be used
as a replacement for the former melody search.

The audio analyzer is implemented as a REST endpoint. However, the search
script must reinitialize indicies on each run, adding inefficiency. Converting
to also be a micro server (ideally via
[AIOHTTP](https://github.com/aio-libs/aiohttp)) could improve performance.
