<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class HomeController extends Controller
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

    /**
     * Search via audio file.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if (  !$request->audio->isValid()
            || $request->audio->getClientMimeType() != "audio/wav")
        {
            return [
                "error" => "Unaccepted mime type.",
            ];
        }

        $searcher  = base_path("../search/searcher.sh");
        $recording = $request->audio->store("recordings");

        $process = new Process([
            $searcher,
            config("search.virtualenv"),
            $recording,
        ]);
        $exitCode = $process->run();
        if ( $exitCode != 0 )
        {
            // TODO: Do not use http response 200.
            return [
                "error"  => "Search failed with code $exitCode.",
                "stdout" => $process->getOutput(),
                "stderr" => $process->getErrorOutput(),
            ];
        }

        return json_decode($process->getOutput());
    }

    /**
     * Search via note csv.
     *
     * @return \Illuminate\Http\Response
     */
    public function searchCSV(Request $request)
    {
        $csv      = $request->getContent();
        $searcher = base_path("../search/searcher2.sh");
        $process  = new Process([
            $searcher,
            config("search.virtualenv"),
        ]);
        $process->setInput( $csv );

        $exitCode = $process->run();
        if ( $exitCode != 0 )
        {
            // TODO: Do not use http response 200.
            return [
                "error"  => "Search failed with code $exitCode.",
                "stdout" => $process->getOutput(),
                "stderr" => $process->getErrorOutput(),
            ];
        }

        return json_decode($process->getOutput());
    }

    public function paper()
    {
        $mdFiles = glob(resource_path("paper/*.md"));
        $hash = "";
        foreach ($mdFiles as $mdFile)
        {
            $hash .= md5_file($mdFile);
        }
        $hash = md5($hash);

        if (!Storage::exists("paper/$hash.pdf"))
        {
            $this->generatePaper($mdFiles, $hash);
        }

        return response()->file(storage_path("app/paper/$hash.pdf"));
    }

    private function generatePaper(array $mdFiles, string $hash)
    {
        $tempPath = tempnam("/tmp", "paper-") . ".pdf";
        $process = new Process(
            array_merge(["pandoc", "-o", $tempPath], $mdFiles)
        );

        $exitCode = $process->run();
        if ($exitCode != 0)
        {
            throw new RuntimeException(
                "`" . $process->getCommandLine() . "` failed ($exitCode)\n"
                . $process->getErrorOutput()
            );
        }

        Storage::putFileAs("paper", new File($tempPath), "$hash.pdf");

        foreach (Storage::files("paper") as $pdf)
        {
            if ($pdf != "paper/$hash.pdf")
            {
                Storage::delete($pdf);
            }
        }
    }
}
