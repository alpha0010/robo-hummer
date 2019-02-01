<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as HttpClient;

class DynamicController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * View the slideshow.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(string $id)
    {
        $slides = [];

        // TODO: Give all versification responsibilities to javascript.
        $verses = [
            'master.dynamic.svg',
            'melody.dynamic.svg',
            'master.dynamic.svg',
            'master.dynamic.svg',
        ];
        $client = new HttpClient();
        foreach ($verses as $verseID => $verse) {
            $resp = (string)$client->get(route("get media", [$id, "{$verse}.info.json"]))->getBody();
            $jsonArray = json_decode($resp, true);

            // 960px seems to be hard-coded into reveal.js as the 'screen width',
            // even scaled down for smaller screens, so moving 960px forward will
            // never skip too far forward, beyond what has already been seen.
            $screenWidth = 960;
            $barLines = $jsonArray['measureBarLines'];
            $offsetBreaks = $this->getOffsetBreaks($barLines, $screenWidth);

            $file = route("get media", [$id, $verse]);
            foreach ($offsetBreaks as $i => $offset) {
                $imageWidth = $jsonArray['width'];
                $nextOffset = ($offsetBreaks[$i + 1] ?? $imageWidth);
                $slides["v{$verseID}"][] =
                    $this->render("v{$verseID}s{$i}", $file, $offset, $nextOffset, $imageWidth);
            }
        }

        return view("slides", ["slides" => $slides]);
    }

    /**
     * @brief Go through each of the $measureOffsets,
     *  and create breakpoints at the measures that are less than $screenWidth apart.
     * @param array $barLines An array of x positions where measure breaks are made.
     * @param int $screenWidth The maximum space between one breakpoint and another.
     * @return array Where the slides should break at.
     */
    private function getOffsetBreaks($barLines, $screenWidth)
    {
        // Where we should break each slide at.
        $offsetBreaks = [];
        $current = 0;
        // Go through each of the $barLines,
        // and use them as breaks that are less than $screenWidth apart.
        foreach ($barLines as $blIndex => $barLine) {
            if ($current == 0) {
                $offsetBreaks[] = $barLine;
                $current++;
            } elseif ($offsetBreaks[$current - 1] + $screenWidth <= $barLine) {
                // If the current line is off the screen,
                // add a slide break at the previous bar line.
                $offsetBreaks[] = $barLines[$blIndex - 1];
                $current++;
            }
        }
        return $offsetBreaks;
    }

    /**
     * @brief Render a slide.
     * @param string $slideName What the slide is called.
     * @param string $file What image should be rendered here.
     * @param int $offset The x-axis pixel offset for this slide to start at.
     * @param int $nextOffset The x-axis pixel offset for this slide to end at.
     * @param int $imageWidth The maximum width of the image.
     * @return array
     */
    private function render(
        string $slideName,
        string $file,
        int $offset,
        int $nextOffset,
        int $imageWidth
    ) {
        $negOffset = 0 - $offset;
        // Shade in the part of the image that the next slide will show.
        $rightNegOffset = $nextOffset - $imageWidth;
        $style = "margin-left:{$negOffset}px;
            box-shadow: inset {$offset}px 0px 0 rgba(127, 127, 127, 0.5),
                        inset {$rightNegOffset}px 0px 0 rgba(127, 127, 127, 0.5);
            width: {$imageWidth}px;
        ";
        // TODO: Give SVG loading responisibilities to javascript.
        $svg = file_get_contents($file);
        return [
            "name"    => $slideName,
            // src='{$file}' data-inline-svg
            "content" => "<div class='dynamic' style='{$style}'>$svg</div>",
        ];
    }
}
