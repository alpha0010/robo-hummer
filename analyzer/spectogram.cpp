#include <iostream>
#include <vector>
#include <marsyas/system/MarSystemManager.h>

int main(int argc, const char** argv)
{
    if (argc != 2)
    {
        std::cout << "Requires filename." << std::endl;
        return 1;
    }

    std::string filename = argv[1];

    Marsyas::MarSystemManager mng;

    Marsyas::MarSystem* net = mng.create("Series", "net");

    net->addMarSystem(mng.create("SoundFileSource", "src"));
    net->addMarSystem(mng.create("Stereo2Mono", "s2m"));
    net->addMarSystem(mng.create("ShiftInput", "si"));
    net->addMarSystem(mng.create("NormMaxMin", "norm"));
    net->addMarSystem(mng.create("Windowing", "win"));
    net->addMarSystem(mng.create("Spectrum", "spk"));
    net->addMarSystem(mng.create("PowerSpectrum", "pspk"));

    net->updControl("SoundFileSource/src/mrs_string/filename", filename);
    net->updControl("SoundFileSource/src/mrs_natural/inSamples", 256);
    net->updControl("ShiftInput/si/mrs_natural/winSize", 2048);
    net->updControl("NormMaxMin/norm/mrs_string/mode", "twopass");
    net->updControl("PowerSpectrum/pspk/mrs_string/spectrumType", "decibels");

    std::vector<double> theStuff;

    while (net->getctrl("SoundFileSource/src/mrs_bool/hasData")
              ->to<Marsyas::mrs_bool>())
    {
        net->tick();
        Marsyas::realvec processedData = net
            ->getctrl("mrs_realvec/processedData")
            ->to<Marsyas::mrs_realvec>();

        // Intensity at each step in the spectrum.
        const int numRows = processedData.getRows();
        for (int r = 0; r < numRows; ++r)
        {
            if (r != 0)
            {
                std::cout << ", ";
            }
            std::cout << processedData(r, 0);
        }
        std::cout << std::endl;
    }

    delete net;

    return 0;
}
