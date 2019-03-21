#!/usr/bin/env python3

import sys
import indexer
import searcher
import simplejson as json


def main(argv):
    filename = argv[1]
    n = 6
    if (len(argv) > 2):
        n = int(argv[2])
        if (n % 2 == 1 or n > 10 or n < 2):
            raise ValueError("n must be an even number between 2 and 10. You gave " + str(n))
    notes = indexer.musicXmlToNotes(filename)
    # For 6-tuples, use 4 (n/2 + 1) as context length.
    contextLen = int((n / 2) + 1)
    feats = searcher.extractAllFeatures(notes, contextLen)
    print(json.dumps(feats, iterable_as_array=True))


if __name__ == "__main__":
    sys.exit(main(sys.argv))