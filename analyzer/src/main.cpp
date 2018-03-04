#include <iostream>

#include "analyzehandler.h"
#include "router.h"
#include "shutdownhandler.h"

int main(int argc, const char** argv)
{
    Router router(std::cout);
    router.route<AnalyzeHandler>("/analyze/*.ogg");
    router.route<ShutdownHandler>("/shutdown");
    router.serve("0.0.0.0", 8080, 1024);

    return 0;
}
