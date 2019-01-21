<?php

namespace App\Http\Controllers;

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

        // TODO: Calculate the number of slides "wide" an individual verse should be.
        $number = 6;

        // TODO: Load each verse into a different section.
        $file = route("get media", [$id, "dynamic.svg"]);
        for ($i = 0; $i < $number; $i++) {
            $slides[0][] = $this->render("Verse 1 slide $i", $file, $i);
        }

        return view("slides", ["slides" => $slides]);
    }

    /**
     * Render a slide.
     *
     * @return array
     */
    private function render(string $slideName, string $file, $number)
    {
        // 960px seems to be hard-coded into reveal.js as the 'screen width',
        // even scaled down for smaller screens, so moving 960px forward will
        // never skip too far forward, beyond what has already been seen.
        $offset = 0 - (960 * $number);
        return [
            "name"    => $slideName,
            "content" => "<img class='dynamic' style='margin-left:{$offset}px;' src='$file'/>",
        ];
    }
}
