#ifndef ANALYZER_H
#define ANALYZER_H

#include <string>

class Analyzer
{
    public:
        Analyzer(std::string file) : m_file(file) {};

        std::string getFeatues();

    private:
        std::string m_file;
};

#endif // ANALYZER_H
