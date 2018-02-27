#ifndef ANALYZEHANDLER_H
#define ANALYZEHANDLER_H

#include "router.h"

class AnalyzeHandler
{
    public:
        /**
         * @brief
         *  Handle an analyze request.
         *
         * @param request
         *  The request.
         *
         * @return
         *  CSV of features in the audio.
         */
        Response handle(Request request);

    private:
        /**
         * @brief
         *  Extract features from a file.
         *
         * @param file
         *  Source audio file.
         *
         * @return
         *  CSV of features in the audio.
         */
        std::string getFeatues(std::string file);
};

#endif // ANALYZEHANDLER_H
