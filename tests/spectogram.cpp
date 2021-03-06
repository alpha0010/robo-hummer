#include <iostream>
#include <memory>
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

    std::unique_ptr<Marsyas::MarSystem> net(mng.create("Series", "net"));

    net->addMarSystem(mng.create("SoundFileSource", "src"));
    net->addMarSystem(mng.create("Stereo2Mono", "s2m"));
    // Composite from MarSystemManager.cpp
    net->addMarSystem(mng.create("PowerSpectrumNet", "pspk"));
    net->addMarSystem(mng.create("MFCC", "mel"));

    net->updControl("SoundFileSource/src/mrs_string/filename", filename);
    net->updControl("SoundFileSource/src/mrs_natural/inSamples", 256);
    net->updControl("PowerSpectrumNet/pspk/mrs_natural/winSize", 2048);
    net->updControl("MFCC/mel/mrs_natural/coefficients", 16);

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

    return 0;
}
