#include "shutdownhandler.h"

Response ShutdownHandler::handle(Request request)
{
    Response response;
    response.body = "Shutting down...";

    timeval delay;
    delay.tv_usec = 100000;
    delay.tv_sec  = 0;
    event_base_loopexit(request.router->getEvBase(), &delay);

    return response;
}
