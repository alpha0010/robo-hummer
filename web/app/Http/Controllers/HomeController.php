<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Parsedown;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class HomeController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("home");
    }

    public function keyboard()
    {
        return view("keyboard");
    }

    public function about()
    {
        $content = $this->renderer->text(
            file_get_contents(resource_path("about.md"))
        );
        return view("about", ["content" => $content]);
    }

    public function paper()
    {
        $mdFiles = glob(resource_path("paper/*.md"));
        $hash = "";
        foreach ($mdFiles as $mdFile) {
            $hash .= md5_file($mdFile);
        }
        $hash = md5($hash);

        if (!Storage::exists("paper/$hash.pdf")) {
            $cwd = getcwd();
            chdir(resource_path("paper"));

            $this->generatePaper($mdFiles, $hash);

            chdir($cwd);
        }

        return response()->file(storage_path("app/paper/$hash.pdf"));
    }

    private function generatePaper(array $mdFiles, string $hash)
    {
        $tempPath = tempnam("/tmp", "paper-") . ".pdf";
        $process = new Process(
            array_merge(["pandoc", "-Vcolorlinks", "-o", $tempPath], $mdFiles)
        );

        $exitCode = $process->run();
        if ($exitCode != 0) {
            throw new RuntimeException(
                "`" . $process->getCommandLine() . "` failed ($exitCode)\n"
                . $process->getErrorOutput()
            );
        }

        Storage::putFileAs("paper", new File($tempPath), "$hash.pdf");

        foreach (Storage::files("paper") as $pdf) {
            if ($pdf != "paper/$hash.pdf") {
                Storage::delete($pdf);
            }
        }
    }
}
