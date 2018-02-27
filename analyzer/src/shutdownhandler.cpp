#include "shutdownhandler.h"

Response ShutdownHandler::handle(Request request)
{
    // It appears libevhtp requires a non-empty response.
    Response response;
    response.body = "Shutting down...";

    // Schedule shutdown; brief delay allows for wrapping up active requests.
    timeval delay;
    delay.tv_usec = 100000;
    delay.tv_sec  = 0;
    event_base_loopexit(request.router->getEvBase(), &delay);

    return response;
}
