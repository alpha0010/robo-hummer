#include "router.h"

#include <arpa/inet.h>
#include <ctime>
#include <iostream>

Router::Router(std::ostream& log)
{
    m_evbase = event_base_new();
    m_htp    = evhtp_new(m_evbase, nullptr);

    // Attach logger.
    evhtp_set_post_accept_cb(
        m_htp,
        [](evhtp_connection_t* conn, void* arg) -> evhtp_res {
            evhtp_hook_request_fini_cb onRequestFinish = [](evhtp_request_t* req, void* arg) -> evhtp_res {
                std::ostream& log = *(std::ostream*)arg;

                char ipAddress[INET6_ADDRSTRLEN];
                sockaddr* saddr = req->conn->saddr;
                const char* ret = nullptr;

                switch (saddr->sa_family)
                {
                    case AF_INET:
                        ret = inet_ntop(
                            AF_INET,
                            &reinterpret_cast<sockaddr_in*>(saddr)->sin_addr,
                            ipAddress,
                            INET6_ADDRSTRLEN
                        );
                        break;

                    case AF_INET6:
                        ret = inet_ntop(
                            AF_INET6,
                            &reinterpret_cast<sockaddr_in6*>(saddr)->sin6_addr,
                            ipAddress,
                            INET6_ADDRSTRLEN
                        );
                        break;

                    default:
                        break;
                }

                if (ret != nullptr)
                {
                    log << ipAddress;
                }
                else
                {
                    log << '-';
                }

                log << " - -";

                std::time_t rawTime;
                std::time(&rawTime);
                char buffer[80];
                std::strftime(buffer, 80, "%d/%b/%Y:%H:%M:%S %z", std::localtime(&rawTime));
                log << " [" << buffer << ']';

                log << " \"" << htparser_get_methodstr_m(req->method) << ' '
                    << req->uri->path->full << ' '
                    << "HTTP/" << (req->proto == EVHTP_PROTO_10 ? "1.0" : "1.1")
                    << '"';

                // TODO: Get response code.
                log << " -";

                const char* contentLen = evhtp_header_find(req->headers_out, "Content-Length");
                if (contentLen != nullptr)
                {
                    log << ' ' << contentLen;
                }
                else
                {
                    log << " -";
                }

                log << " \"-\"";

                const char* userAgent = evhtp_header_find(req->headers_in, "User-Agent");
                if (userAgent != nullptr)
                {
                    log << " \"" << userAgent << '"';
                }
                else
                {
                    log << " \"-\"";
                }

                log << std::endl;

                return EVHTP_RES_OK;
            };

            evhtp_set_hook(
                &conn->hooks,
                evhtp_hook_on_request_fini,
                reinterpret_cast<evhtp_hook>(onRequestFinish),
                arg
            );
            return EVHTP_RES_OK;
        },
        &log
    );
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
            evhtp_headers_add_header(
                req->headers_out,
                evhtp_header_new("Content-Disposition", "attachment; filename=\"result.csv\"", 0, 0)
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
