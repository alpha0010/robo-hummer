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
	read -p "Run the cron script now? " answer
	echo "$file not found."
fi

# 4 is multi pitch estimation Melodia
./standard_pitchdemo $1 file.csv 4
cat file.csv

# Found formula online
# freq = (440. * math.exp(.057762265 * (midi - 69.)));
# f = 440 * e^{.057762265 * (m - 69)}
# solved by wolfram alpha
# m = 17.3123 log(0.122312 f) for f>0
# midi = 17.3123 ln(0.122312 * freq )

