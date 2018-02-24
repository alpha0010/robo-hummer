#include <evhtp.h>
#include <iostream>
#include <regex>

#include "analyzer.h"

int main(int argc, const char** argv)
{
    evbase_t* evbase = event_base_new();
    evhtp_t*  htp    = evhtp_new(evbase, nullptr);

    // Process.
    evhtp_set_glob_cb(
        htp,
        "/analyze/*.ogg",
        [](evhtp_request_t* req, void* data) {
            evhtp_headers_add_header(
                req->headers_out,
                evhtp_header_new("Content-Type", "text/csv", 0, 0)
            );

            std::string features;

            std::regex nameExtractor("^/analyze/(([a-z0-9]+/)*[a-z0-9]+\\.ogg)$");
            std::cmatch matches;
            if (std::regex_match(req->uri->path->full, matches, nameExtractor))
            {
                Analyzer analyzer(matches[1]);
                features = analyzer.getFeatues();
            }

            if (features.empty())
            {
                // TODO: Handle match failure better.
                features = "Uh oh...";
            }

            char* csvBuff = new char[features.size()];
            features.copy(csvBuff, features.size());

            evbuffer_add_reference(
                req->buffer_out,
                csvBuff,
                features.size(),
                [](const void* charBuffer, size_t datLen, void* extra) {
                    delete (char*)charBuffer;
                },
                nullptr
            );

            evhtp_send_reply(req, EVHTP_RES_OK);
        },
        evbase
    );

    // 404 not found.
    evhtp_set_gencb(
        htp,
        [](evhtp_request_t* req, void* data) {
            evhtp_headers_add_header(
                req->headers_out,
                evhtp_header_new("Content-Type", "text/html; charset=UTF-8", 0, 0)
            );
            evbuffer_add_reference(
                req->buffer_out, "<!DOCTYPE html><html><h1>Not found</h1></html>", 46, nullptr, nullptr
            );

            evhtp_send_reply(req, EVHTP_RES_NOTFOUND);
        },
        nullptr
    );

    // Shutdown.
    evhtp_set_cb(
        htp,
        "/shutdown/",
        [](evhtp_request_t* req, void* data) {
            evbuffer_add_reference(
                req->buffer_out, "Shutting down...", 16, nullptr, nullptr
            );
            evhtp_send_reply(req, EVHTP_RES_OK);

            timeval delay;
            delay.tv_usec = 100000;
            delay.tv_sec  = 0;
            event_base_loopexit((evbase_t*)data, &delay);
        },
        evbase
    );

    std::cout << "Starting server." << std::endl;

    evhtp_bind_socket(htp, "0.0.0.0", 8080, 1024);
    event_base_loop(evbase, 0);

    std::cout << "Server closed." << std::endl;

    evhtp_unbind_socket(htp);
    evhtp_free(htp);
    event_base_free(evbase);

    std::cout << "Shutdown complete." << std::endl;
    return 0;
}
