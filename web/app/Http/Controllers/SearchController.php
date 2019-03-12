<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Parsedown;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class SearchController extends Controller
{

    /**
     * Search via audio file.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if (!$request->audio->isValid()
            || $request->audio->getClientMimeType() != "audio/wav") {
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
        if ($exitCode != 0) {
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
        $process  = new Process([
            "sudo", "-u", "python",
            "/var/www/tools/searcher.py", "--csv",
        ]);
        $process->setInput($csv);

        $exitCode = $process->run();
        if ($exitCode != 0) {
            // TODO: Do not use http response 200.
            return [
                "error"  => "Search failed with code $exitCode.",
                "stdout" => $process->getOutput(),
                "stderr" => $process->getErrorOutput(),
            ];
        }

        $results = json_decode($process->getOutput());
        return $this->addTitles($results);
    }

    /**
     * @brief Adds title and path for search results.
     */
    private function addTitles($results)
    {
        foreach ($results as &$result) {
            // TODO: Lookup media file's title and URL.
            $result->title = $result->name;
            $parts = explode("/", $result->name);
            array_pop($parts);
            $id = end($parts);
            $result->path = "/media/$id/harmony.mp3";
        }
        return $results;
    }
}
