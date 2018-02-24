#ifndef ANALYZEHANDLER_H
#define ANALYZEHANDLER_H

#include "router.h"

class AnalyzeHandler
{
    public:
        Response handle(Request request);

    private:
        std::string getFeatues(std::string file);
};

#endif // ANALYZEHANDLER_H
