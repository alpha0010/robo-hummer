<?php

namespace App\Http\Controllers;

use App\Media;
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
        // Allow negative numbers to get you to newer media entries.
        if ($id < 0) {
            // -1 gets you the newest media item, -2 gets you the second newest...
            $id2 = Media::orderBy('id', 'desc')->skip(($id * -1) - 1)->first()->id;
            return redirect("/dynamic/$id2");
        }

        $slides = [];

        // TODO: Give all versification responsibilities to javascript.
        $verses = [
            1 => 'master.dynamic.svg',
            2 => 'master.dynamic.svg',
            3 => 'master.dynamic.svg',
            4 => 'master.dynamic.svg',
        ];
        $client = new HttpClient();
        foreach ($verses as $verseID => $verse) {
            $file = route("get media", [$id, $verse]);
            $slides["v{$verseID}"][] =
                $this->render("v{$verseID}s0", $file);
        }
        $audio = route("get media", [$id, "harmony.mp3"]);
        return view("slides", ["slides" => $slides, 'audio' => $audio]);
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
        // TODO: Implement measure-based breaks in javascript.
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
     * @return array
     */
    private function render(
        string $slideName,
        string $file
    ) {
        // TODO: Give SVG loading responisibilities to javascript.
        $svg = file_get_contents($file);
        return [
            "name"    => $slideName,
            // src='{$file}' data-inline-svg
            "content" => "<div class='dynamic original' style='opacity: 0;' data-page='0'>$svg</div>",
        ];
    }
}
