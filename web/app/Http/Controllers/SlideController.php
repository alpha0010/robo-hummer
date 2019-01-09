<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Parsedown;
use Symfony\Component\Process\Process;

class SlideController extends Controller
{
    private $renderer;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Parsedown $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * View the slideshow.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(string $name)
    {
        $indexFile = resource_path("slides/$name/index.json");
        if (!is_readable($indexFile)) {
            abort(404);
        }

        $index = json_decode(file_get_contents($indexFile), true);

        $slides = [];
        foreach ($index[ "slides" ] as $slideName) {
            $slides[] = $this->render(
                $slideName,
                resource_path("slides/$name")
            );
        }

        return view("slides", ["slides" => $slides]);
    }

    /**
     * Render a slide.
     *
     * @return array
     */
    private function render(string $slideName, string $resourcePath)
    {
        return [
            "name"    => $slideName,
            "content" => $this->renderer->text(
                file_get_contents("$resourcePath/$slideName.md")
            ),
        ];
    }
}
