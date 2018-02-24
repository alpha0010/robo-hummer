#ifndef ROUTER_H
#define ROUTER_H

#include <evhtp.h>
#include <string>

class Router;

struct Request
{
    Request(evhtp_request_t* _req, Router* _router) :
        router(_router),
        path(_req->uri->path->full)
    {}

    Router* router;
    const char* path;
};

enum class ContentType
{
    csv,
    html,
    text
};

struct Response
{
    std::string body;
    ContentType contentType = ContentType::text;
};

class Router
{
    public:
        Router();
        ~Router();

        template<typename Handler>
        void route(std::string path);

        void serve(const char* addr, uint16_t port, int backlog);

        Request makeRequest(evhtp_request_t* req);
        void sendReply(evhtp_request_t* req, const Response& response);

        evbase_t* getEvBase() { return m_evbase; }

    private:
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
        evhtp_set_cb(m_htp, path.c_str(), callback, this);
    }
    else
    {
        evhtp_set_glob_cb(m_htp, path.c_str(), callback, this);
    }
}
#endif // ROUTER_H
