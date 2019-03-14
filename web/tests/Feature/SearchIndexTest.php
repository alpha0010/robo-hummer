<?php

namespace Tests\Feature;

use Artisan;
use App\Http\Controllers\SearchController;
use App\Media;
use Tests\ClearMedia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class SearchIndexTest extends TestCase
{
    use RefreshDatabase;
    use ClearMedia;

    public function testSearchIndex()
    {
        // Create media files
        $localPath = "../examplemedia/1/melody.musicxml";
        $exitCode = Artisan::call("media:create",['file' => $localPath]);
        $this->assertEquals(0, $exitCode);

        $localPath = "../examplemedia/4/harmony.musicxml";
        $exitCode = Artisan::call("media:create",['file' => $localPath]);
        $this->assertEquals(0, $exitCode);

        // Create the tuples
        $exitCode = Artisan::call("media:cache",['--type' => '6.tuples.json']);
        $this->assertEquals(0, $exitCode);

        $mediaPath = Media::getAbsDir();
        $indexPath = SearchController::getIndexPath();
        $process = new Process([
            "bash", "-c",
            // Note: This command should match the dart reindex command, but with different directories.
            "find {$mediaPath} -perm /o+r -name 6.tuples.json "
                . "| sudo -u python ../tools/indexer.py {$indexPath}"
        ]);
        $process->run();
        $this->assertTrue($process->isSuccessful());
    }
}
