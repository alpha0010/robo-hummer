#include "router.h"

#include <iostream>

Router::Router()
{
    m_evbase = event_base_new();
    m_htp    = evhtp_new(m_evbase, nullptr);
}

Router::~Router()
{
    evhtp_free(m_htp);
    event_base_free(m_evbase);

    std::cout << "Shutdown complete." << std::endl;
}

void Router::serve(const char* addr, uint16_t port, int backlog)
{
    std::cout << "Starting server." << std::endl;

    evhtp_bind_socket(m_htp, addr, port, backlog);
    event_base_loop(m_evbase, 0);

    std::cout << "Server closed." << std::endl;

    evhtp_unbind_socket(m_htp);
}

Request Router::makeRequest(evhtp_request_t* req)
{
    return Request(this, req->uri->path->full);
}

void Router::sendReply(evhtp_request_t* req, const Response& response)
{
    // Add headers.
    switch (response.contentType)
    {
        case ContentType::csv:
            evhtp_headers_add_header(
                req->headers_out,
                evhtp_header_new("Content-Type", "text/csv", 0, 0)
            );
            break;

        case ContentType::html:
            evhtp_headers_add_header(
                req->headers_out,
                evhtp_header_new("Content-Type", "text/html; charset=UTF-8", 0, 0)
            );
            break;

        default:
            break;
    }

    // Allocate and bind response buffer.
    char* buffer = new char[response.body.size()];
    response.body.copy(buffer, response.body.size());

    evbuffer_add_reference(
        req->buffer_out,
        buffer,
        response.body.size(),
        [](const void* charBuffer, size_t /* datLen */, void* /* extra */) {
            delete (char*)charBuffer;
        },
        nullptr
    );

    // Send.
    evhtp_send_reply(req, EVHTP_RES_OK);
}
