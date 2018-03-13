#include "midihandler.h"

#include <cmath>
#include <marsyas/system/MarSystemManager.h>
#include <memory>
#include <numeric>
#include <regex>

Response MidiHandler::handle(Request request)
{
    std::string notes;

    // Basic url validation to prevent root escape.
    std::regex nameExtractor("^/midi/(([a-z0-9-]+/)*[a-z0-9]+\\.ogg)$");
    std::cmatch matches;
    if (std::regex_match(request.path, matches, nameExtractor))
    {
        notes = getNotes(matches[1]);
    }

    if (notes.empty())
    {
        // TODO: Handle failure better.
        notes = "Failed to extract notes.";
    }

    Response response;
    response.body = notes;
    response.contentType = ContentType::csv;

    return response;
}

std::string MidiHandler::getNotes(std::string file)
{
    // Build the analyzer.
    Marsyas::MarSystemManager mng;

    std::unique_ptr<Marsyas::MarSystem> net(mng.create("Series", "net"));

    // Guess frequency.
    net->addMarSystem(mng.create("SoundFileSource", "src"));
    net->addMarSystem(mng.create("Stereo2Mono", "s2m"));
    net->addMarSystem(mng.create("ShiftInput", "si"));
    net->addMarSystem(mng.create("AubioYin", "yin"));

    net->updControl("SoundFileSource/src/mrs_string/filename", file);

    // Extract notes.
    std::vector<double> notes;

    while (net->getctrl("SoundFileSource/src/mrs_bool/hasData")
              ->to<Marsyas::mrs_bool>())
    {
        net->tick();
        Marsyas::realvec processedData = net
            ->getctrl("mrs_realvec/processedData")
            ->to<Marsyas::mrs_realvec>();

        double frequency = processedData(0, 0);
        double note = -1;
        // Filter out extremely high and low (they are unlikely correct).
        // See: https://en.wikipedia.org/wiki/Vocal_range .
        if (frequency > 70 && frequency < 1500)
        {
            note = 69 + 12 * log2(frequency / 440);
        }

        notes.push_back(note);
    }

    if (notes.size() < 3)
    {
        return "";
    }

    // Median of 3.
    // TODO: Consider median of 5 (replace "3" with "5", and "1" with "2").
    double buffer[3];
    std::vector<double> smoothed;
    smoothed.reserve(notes.size());
    for (size_t i = 0; i < notes.size() - 3; ++i)
    {
        std::copy_n(notes.begin() + i, 3, buffer);
        std::nth_element(buffer, buffer + 1, buffer + 3);
        smoothed.push_back(buffer[1]);
    }

    // Detect boundaries.
    std::vector<size_t> boundaries;
    boundaries.push_back(0);
    double currVal = smoothed.front();
    double runningAvg = currVal;
    for (size_t i = 0; i < smoothed.size(); ++i)
    {
        runningAvg = (2 * runningAvg + smoothed[i]) / 3;
        // TODO: Test different thresholds.
        if (std::abs(currVal - runningAvg) > 0.4
            && boundaries.back() < i - 1)
        {
            boundaries.push_back(i - 1);
            currVal = smoothed[i];
        }
    }
    boundaries.push_back(smoothed.size());

    // Find median note length.
    std::vector<size_t> noteLengths;
    noteLengths.reserve(boundaries.size());
    for (size_t i = 0; i < boundaries.size() - 1; ++i)
    {
        noteLengths.push_back(boundaries[i + 1] - boundaries[i]);
    }
    size_t medianIndex = noteLengths.size() / 2;
    std::nth_element(
        noteLengths.begin(),
        noteLengths.begin() + medianIndex,
        noteLengths.end()
    );
    size_t medianNoteLen = noteLengths[medianIndex];

    // Remove short notes (probably detection error).
    // TODO: Test different thresholds.
    size_t minNoteLen = medianNoteLen * 0.44;
    // Bad O(n), but probably okay in this context.
    for (size_t i = 0; i < boundaries.size() - 1;)
    {
        if (boundaries[i + 1] - boundaries[i] < minNoteLen)
        {
            boundaries[i] = (boundaries[i] + boundaries[i + 1]) / 2;
            boundaries.erase(boundaries.begin() + i + 1);
        }
        else
        {
            ++i;
        }
    }

    // Create boundary graph.
    std::vector<double> detectedNotes(smoothed);
    for (size_t i = 0; i < boundaries.size() - 1; ++i)
    {
        size_t lower = boundaries[i];
        size_t upper = boundaries[i + 1];
        size_t mid = (lower + upper) / 2;

        std::nth_element(
            detectedNotes.begin() + lower,
            detectedNotes.begin() + mid,
            detectedNotes.begin() + upper
        );

        std::fill(
            detectedNotes.begin() + lower,
            detectedNotes.begin() + upper,
            detectedNotes[mid]
        );
    }

    // Combine nearly-same notes.
    // Bad O(n), but probably okay in this context.
    for (size_t i = 0; i < boundaries.size() - 1;)
    {
        double noteA = detectedNotes[boundaries[i]];
        double noteB = detectedNotes[boundaries[i + 1]];

        if (std::abs(noteA - noteB) < 0.35)
        {
            boundaries.erase(boundaries.begin() + i + 1);
        }
        else
        {
            ++i;
        }
    }
    for (size_t i = 0; i < boundaries.size() - 1; ++i)
    {
        size_t lower = boundaries[i];
        size_t upper = boundaries[i + 1];

        double average = std::accumulate(
            detectedNotes.begin() + lower,
            detectedNotes.begin() + upper,
            0.0
        ) / (upper - lower);

        std::fill(
            detectedNotes.begin() + lower,
            detectedNotes.begin() + upper,
            average
        );
    }

    // Output the data.
    std::ostringstream output;
    for (size_t i = 0; i < smoothed.size(); ++i)
    {
        output << notes[i + 1]     << ", "
               << smoothed[i]      << ", "
               << detectedNotes[i] << std::endl;
    }
    return output.str();
}
