<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     * Show the application dashboard.
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
            return [
                "error" => "Search failed with code $exitCode.",
            ];
        }

        return json_decode($process->getOutput());
    }
    public function searchCSV(Request $request)
    {
		$csv = $request->getContent();
        $searcher  = base_path("../search/searcher2.sh");
        $process = new Process([
            $searcher,
            config("search.virtualenv"),
        ]);
        $process->setInput( $csv );

        $exitCode = $process->run();
        if ( $exitCode != 0 )
        {
            return [
                "error" => "Search failed with code $exitCode.",
            ];
        }

        return json_decode($process->getOutput());
	}
}
