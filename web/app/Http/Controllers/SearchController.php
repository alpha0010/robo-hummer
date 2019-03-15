<?php

namespace App\Http\Controllers;

use App;
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
            SearchController::getIndexPath(),
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
            "/var/www/tools/searcher.py",
            SearchController::getIndexPath(),
            "--csv",
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
        return $this->addData($results);
    }

    /**
     * @brief Add additional data for search results.
     */
    private function addData($results)
    {
        foreach ($results as &$result) {
            // TODO: Lookup media file's title and URL.
            $result->title = "$result->name ({$result->score})";
            $parts = explode("/", $result->name);
            array_pop($parts);
            $id = end($parts);
            $result->robohummer_media_id = $id;
            $result->path = "/media/$id/harmony.mp3";
        }
        return $results;
    }

    /**
     * @brief return the path used for storing the melody index.
     */
    public static function getIndexPath()
    {
        if (App::environment("testing")) {
            return "/tmp";
        }
        return "/var/www/melodyindex";
    }
}
