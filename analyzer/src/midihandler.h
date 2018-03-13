#ifndef MIDIHANDLER_H
#define MIDIHANDLER_H

#include "router.h"

class MidiHandler
{
    public:
        /**
         * @brief
         *  Handle midi note guess request.
         *
         * @param request
         *  The request.
         *
         * @return
         *  CSV of midi notes in audio.
         */
        Response handle(Request request);

    private:
        /**
         * @brief
         *  Extract midi notes from a file.
         *
         * @param file
         *  Source audio file.
         *
         * @return
         *  CSV of guessed midi notes in the audio.
         */
        std::string getNotes(std::string file);
};

#endif // MIDIHANDLER_H
