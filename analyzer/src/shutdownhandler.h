#ifndef SHUTDOWNHANDLER_H
#define SHUTDOWNHANDLER_H

#include "router.h"

class ShutdownHandler
{
    public:
        /**
         * @brief
         *  Handle a shutdown request.
         *
         * Calling this will initiate shutdown of the server.
         *
         * @param request
         *  The request.
         *
         * @return
         *  Acknowledgement of request.
         */
        Response handle(Request request);
};

#endif // SHUTDOWNHANDLER_H
