#include <iostream>

#include "analyzehandler.h"
#include "midihandler.h"
#include "router.h"
#include "shutdownhandler.h"

int main(int argc, const char** argv)
{
    // TODO: Log to file stream instead of cout.
    Router router(std::cout);
    router.route<AnalyzeHandler>("/analyze/*");
    router.route<MidiHandler>("/midi/*");
    router.route<ShutdownHandler>("/shutdown");
    router.serve("0.0.0.0", 8080, 1024);

    return 0;
}
