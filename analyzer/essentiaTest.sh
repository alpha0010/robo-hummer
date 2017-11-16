# Make your input file mono (I think otherwise, some of these only read right channel?)
# Download extractors from http://essentia.upf.edu/documentation/extractors/

if [ $# -eq 0 ]
  then
    echo "Usage ./essentialTest.sh filename.wav"
	exit
fi

if [ -h "./standard_pitchdemo" ]
then
	echo ""
else
	echo "Please place the file standard_pitchdemo in the working directory"
fi

# 4 is multi pitch estimation Melodia
./standard_pitchdemo $1 file.csv 4
cat file.csv
