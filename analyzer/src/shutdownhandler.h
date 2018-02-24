#ifndef SHUTDOWNHANDLER_H
#define SHUTDOWNHANDLER_H

#include "router.h"

class ShutdownHandler
{
    public:
        Response handle(Request request);
};

#endif // SHUTDOWNHANDLER_H
