#!/bin/bash

cd "$(dirname "$0")"
source $1/bin/activate

python searcher.py --csv
EXIT_CODE=$?

exit $EXIT_CODE
