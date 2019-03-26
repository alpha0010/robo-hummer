<?php

namespace Tests\Feature;

use Artisan;
use App\Http\Controllers\SearchController;
use App\Media;
use Tests\ClearMedia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Process\Process;

class SearchIndexTest extends TestCase
{
    use RefreshDatabase;
    use ClearMedia;

    public function testSearchIndex()
    {
        // Create media files
        // Amazing Grace.
        $localPath = "../examplemedia/1/melody.musicxml";
        $exitCode = Artisan::call("media:create", ['file' => $localPath]);
        $this->assertEquals(0, $exitCode);

        // This file is Holy, Holy, Holy!
        $localPath = "../examplemedia/4/harmony.musicxml";
        $exitCode = Artisan::call("media:create", ['file' => $localPath]);
        $this->assertEquals(0, $exitCode);

        // Create the tuples
        $exitCode = Artisan::call("media:cache", ['--type' => '6.tuples.json']);
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

        // Holy, Holy Holy! perfectly inputted at 120bpm.
        $csv = "60,500\n"// Ho-
            . "60,500\n" // -ly,
            . "64,500\n" // Ho-
            . "64,500\n" // -ly,
            . "67,1000\n"// Ho-
            . "67,1000\n"// -ly!
            . "69,1000\n"// Lord
            . "69,500\n" // God
            . "69,500\n" // Al-
            . "67,1000\n"// -might-
            . "64,1000\n"// -y!
        ;

        $response = $this->call('POST', '/api/uploadCSV', [], [], [], [], $csv);
        $response->assertOk();

        // Make sure that "Holy, Holy, Holy!" is the first result.
        $response->assertJson([
            ['robohummer_media_id' => 2],
            ['robohummer_media_id' => 1],
        ]);

        // Amazing Grace, perfectly inputted at 120bpm.
        $csv = "60,500\n"
            . "65,1000\n"
            . "69,250\n"
            . "65,250\n"
            . "69,1000\n"
            . "67,500\n"
            . "65,1000\n"
            . "62,500\n"
            . "60,1000\n"
            . "60,500\n"
            . "65,1000\n"
            . "69,250\n"
            . "65,250\n"
            . "69,1000\n"
            . "67,500\n"
            . "72,2500\n"
        ;
        $response = $this->call('POST', '/api/uploadCSV', [], [], [], [], $csv);
        $response->assertOk();

        $response->assertJson([
            ['robohummer_media_id' => 1],
            ['robohummer_media_id' => 2],
        ]);
    }
}
