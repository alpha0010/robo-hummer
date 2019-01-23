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
        $slides = [[]];

        $client = new HttpClient();
        $resp = (string)$client->get(route("get media", [$id, "dynamic.svg.info.json"]))->getBody();
        $jsonArray = json_decode($resp, true);

        // 960px seems to be hard-coded into reveal.js as the 'screen width',
        // even scaled down for smaller screens, so moving 960px forward will
        // never skip too far forward, beyond what has already been seen.
        $screenWidth = 960;
        $offsetBreaks = $this->getOffsetBreaks($jsonArray['measureOffsets'], $screenWidth);

        // TODO: Load each verse into a different section.
        $file = route("get media", [$id, "dynamic.svg"]);
        $i = 0;
        foreach ($offsetBreaks as $offset) {
            $imageWidth = $jsonArray['width'];
            $nextOffset = ($offsetBreaks[$i + 1] ?? $imageWidth);
            $slides[0][] = $this->render("Verse 1 slide $i", $file, $offset, $nextOffset, $imageWidth);
            $i++;
        }

        return view("slides", ["slides" => $slides]);
    }

    /**
     * @brief Go through each of the $measureOffsets,
     *  and create breakpoints at the measures that are less than $screenWidth apart.
     * @param array $measureOffsets An array of x positions where measure breaks are made.
     * @param int $screenWidth The maximum space between one breakpoint and another.
     * @return array Where the slides should break at.
     */
    private function getOffsetBreaks($measureOffsets, $screenWidth)
    {
        // Where we should break each slide at.
        $offsetBreaks = [];
        $current = 0;
        // Go through each of the measureOffsets,
        // and use them as breaks that are less than $screenWidth apart.
        foreach ($measureOffsets as $number => $measureOffset) {
            if ($current == 0) {
                $offsetBreaks[] = $measureOffset;
                $current++;
            } elseif ($offsetBreaks[$current - 1] + $screenWidth <= $measureOffset) {
                // If the current offset is too far away, break at the previous offset.
                $offsetBreaks[] = $measureOffsets[$number - 1];
                $current++;
            }
        }
        return $offsetBreaks;
    }

    /**
     * Render a slide.
     *
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
        ";
        return [
            "name"    => $slideName,
            "content" => "<img class='dynamic' style='{$style}' src='{$file}'/>",
        ];
    }
}
