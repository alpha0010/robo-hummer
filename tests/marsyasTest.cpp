#include <iostream>
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

    Marsyas::MarSystem* net = mng.create("Series", "series");

    net->addMarSystem(mng.create("SoundFileSource", "src"));
    net->addMarSystem(mng.create("ShiftInput", "si"));
    net->addMarSystem(mng.create("AubioYin", "yin"));

    net->updControl("SoundFileSource/src/mrs_string/filename", filename);

    while (net->getctrl("SoundFileSource/src/mrs_bool/hasData")
              ->to<Marsyas::mrs_bool>())
    {
        net->tick();
        Marsyas::realvec r = net
            ->getctrl("mrs_realvec/processedData")
            ->to<Marsyas::mrs_realvec>();

        // Approximate guessed frequency of note.
        std::cout << r(0, 0) << std::endl;
    }

    delete net;

    return 0;
}
