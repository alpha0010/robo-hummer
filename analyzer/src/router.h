#ifndef ROUTER_H
#define ROUTER_H

#include <evhtp.h>
#include <iosfwd>
#include <string>

class Router;

struct Request
{
    Request(Router* _router, const char* _path) :
        router(_router),
        path(_path)
    {}

    Router* router;
    const char* path;
};

enum class ContentType
{
    csv,  //!< "text/csv"
    html, //!< "text/html; charset=UTF-8"
    text  //!< "text/plain"
};

struct Response
{
    std::string body;
    ContentType contentType = ContentType::text;
};

class Router
{
    public:
        Router(std::ostream& log);
        ~Router();

        /**
         * @brief
         *  Register a route.
         *
         * @parm path
         *  The path to handle; supports `*` for wildcards.
         *
         * @tparam Handler
         *  Class to call when this route is triggered.
         */
        template<typename Handler>
        void route(std::string path);

        /**
         * @brief
         *  Launch the web server.
         *
         * This call is blocking.
         *
         * @param addr
         *  Binding address. Prefix with `ipv6:`, `unix:`, or `ipv4:` to
         *  specify type. Unprefixed is assumed ipv4.
         * @param port
         *  Port number to bind to.
         * @param backlog
         *  Number of pending connections to allow.
         */
        void serve(const char* addr, uint16_t port, int backlog);

        /**
         * @brief
         *  Access the libevent event loop.
         *
         * @return
         *  The libevent event loop.
         */
        evbase_t* getEvBase() { return m_evbase; }

    private:
        /**
         * @brief
         *  Build a Request object.
         *
         * @param req
         *  The libevhtp request struct.
         *
         * @return
         *  The Request object.
         */
        Request makeRequest(evhtp_request_t* req);

        /**
         * @brief
         *  Send a response to back to the client.
         *
         * @param req
         *  The libevhtp request struct of this request.
         * @param response
         *  The response to send.
         */
        void sendReply(evhtp_request_t* req, const Response& response);

        evbase_t* m_evbase;
        evhtp_t*  m_htp;
};


template<typename Handler>
void Router::route(std::string path)
{
    auto callback = [](evhtp_request_t* req, void* data) {
        Router* router = static_cast<Router*>(data);

        Handler handler;
        Response response = handler.handle(router->makeRequest(req));

        router->sendReply(req, response);
    };

    if (path.find('*') == std::string::npos)
    {
        // Register as static path.
        evhtp_set_cb(m_htp, path.c_str(), callback, this);
    }
    else
    {
        // Register with wildcards.
        evhtp_set_glob_cb(m_htp, path.c_str(), callback, this);
    }
}
#endif // ROUTER_H
