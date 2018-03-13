#include "analyzehandler.h"

#include <marsyas/system/MarSystemManager.h>
#include <memory>
#include <regex>

Response AnalyzeHandler::handle(Request request)
{
    std::string features;

    // Basic url validation to prevent root escape.
    std::regex nameExtractor("^/analyze/(([a-z0-9-]+/)*[a-z0-9]+\\.ogg)$");
    std::cmatch matches;
    if (std::regex_match(request.path, matches, nameExtractor))
    {
        features = getFeatues(matches[1]);
    }

    if (features.empty())
    {
        // TODO: Handle failure better.
        features = "Uh oh...";
    }

    Response response;
    response.body = features;
    response.contentType = ContentType::csv;

    return response;
}

std::string AnalyzeHandler::getFeatues(std::string file)
{
    // Build the analyzer.
    Marsyas::MarSystemManager mng;

    std::unique_ptr<Marsyas::MarSystem> net(mng.create("Series", "net"));

    net->addMarSystem(mng.create("SoundFileSource", "src"));
    net->addMarSystem(mng.create("Stereo2Mono", "s2m"));
    // Composite from MarSystemManager.cpp
    net->addMarSystem(mng.create("PowerSpectrumNet", "pspk"));
    net->addMarSystem(mng.create("MFCC", "mel"));

    net->updControl("SoundFileSource/src/mrs_string/filename", file);
    net->updControl("SoundFileSource/src/mrs_natural/inSamples", 256);
    net->updControl("PowerSpectrumNet/pspk/mrs_natural/winSize", 2048);
    net->updControl("MFCC/mel/mrs_natural/coefficients", 16);

    // Extract features.
    std::ostringstream features;

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
                features << ", ";
            }
            features << processedData(r, 0);
        }
        features << std::endl;
    }

    return features.str();
}
