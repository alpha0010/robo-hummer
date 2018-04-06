#!/bin/bash

cd "$(dirname "$0")"
source $1/bin/activate

python searcher.py $2
EXIT_CODE=$?

exit $EXIT_CODE
