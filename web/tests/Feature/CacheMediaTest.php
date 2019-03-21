<?php

namespace Tests\Feature;

use Artisan;
use App\Media;
use DateTime;
use Tests\ClearMedia;
use Tests\ClearDeleteMediaTrait;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class CacheMediaTest extends TestCase
{
    use RefreshDatabase;
    use ClearMedia;
    use ClearDeleteMediaTrait;

    public function testCacheType()
    {
        $this->setupFilesMore();
        $this->sleep();
        $date = new DateTime();
        $this->sleep();
        $exitCode = Artisan::call("media:cache", ['--type' => "harmony.midi"]);
        $this->assertEquals(0, $exitCode);

        $this->assertExists("/1/harmony.midi");
        $this->assertExists("/2/harmony.midi");
        $this->assertNotExists("/3/harmony.midi");
        $this->assertExists("/4/harmony.midi");
        $this->assertExists("/5/harmony.midi");

        $this->assertInerrant("/1/harmony.midi");
        $this->assertInerrant("/2/harmony.midi");
        $this->assertErrant("/4/harmony.midi");
        $this->assertInerrant("/5/harmony.midi");

        $this->assertOlder("/1/harmony.midi", $date);
        $this->assertOlder("/2/harmony.midi", $date);
        $this->assertNewer("/4/harmony.midi", $date);
        $this->assertNewer("/5/harmony.midi", $date);
    }

    public function testRecacheAll()
    {
        $this->testCacheType();
        $this->sleep();
        $date = new DateTime();
        $this->sleep();
        $exitCode = Artisan::call("media:cache", ['--type' => "harmony.midi", '--recache' => 'all']);
        $this->assertEquals(0, $exitCode);

        $this->assertInerrant("/1/harmony.midi");
        $this->assertInerrant("/2/harmony.midi");
        $this->assertErrant("/4/harmony.midi");
        $this->assertInerrant("/5/harmony.midi");

        $this->assertExists("/1/harmony.midi");
        $this->assertExists("/2/harmony.midi");
        $this->assertNotExists("/3/harmony.midi");
        $this->assertExists("/4/harmony.midi");
        $this->assertExists("/5/harmony.midi");

        $this->assertOlder("/1/harmony.midi", $date);
        $this->assertNewer("/2/harmony.midi", $date);
        $this->assertNewer("/4/harmony.midi", $date);
        $this->assertNewer("/5/harmony.midi", $date);
    }
    public function testRecacheErrors()
    {
        $this->testCacheType();
        $this->sleep();
        $date = new DateTime();
        $this->sleep();
        $exitCode = Artisan::call("media:cache", ['--type' => "harmony.midi", '--recache' => 'errors']);
        $this->assertEquals(0, $exitCode);

        $this->assertInerrant("/1/harmony.midi");
        $this->assertInerrant("/2/harmony.midi");
        $this->assertErrant("/4/harmony.midi");
        $this->assertInerrant("/5/harmony.midi");

        $this->assertExists("/1/harmony.midi");
        $this->assertExists("/2/harmony.midi");
        $this->assertNotExists("/3/harmony.midi");
        $this->assertExists("/4/harmony.midi");
        $this->assertExists("/5/harmony.midi");

        $this->assertOlder("/1/harmony.midi", $date);
        $this->assertOlder("/2/harmony.midi", $date);
        $this->assertNewer("/4/harmony.midi", $date);
        $this->assertOlder("/5/harmony.midi", $date);
    }
    public function testRecacheSuccesses()
    {
        $this->testCacheType();
        $this->sleep();
        $date = new DateTime();
        $this->sleep();
        $exitCode = Artisan::call("media:cache", ['--type' => "harmony.midi", '--recache' => 'successes']);
        $this->assertEquals(0, $exitCode);

        $this->assertInerrant("/1/harmony.midi");
        $this->assertInerrant("/2/harmony.midi");
        $this->assertErrant("/4/harmony.midi");
        $this->assertInerrant("/5/harmony.midi");

        $this->assertExists("/1/harmony.midi");
        $this->assertExists("/2/harmony.midi");
        $this->assertNotExists("/3/harmony.midi");
        $this->assertExists("/4/harmony.midi");
        $this->assertExists("/5/harmony.midi");

        $this->assertOlder("/1/harmony.midi", $date);
        $this->assertNewer("/2/harmony.midi", $date);
        $this->assertOlder("/4/harmony.midi", $date);
        $this->assertNewer("/5/harmony.midi", $date);
    }

    private function assertErrant($file)
    {
        $this->assertEquals('private', Storage::getVisibility(Media::getDir() . $file),
            "file '$file' should have been an error."
        );
    }
    private function assertInerrant($file)
    {
        $this->assertEquals('public', Storage::getVisibility(Media::getDir() . $file),
            "file '$file' should not have been an error."
        );
    }
    private function assertNewer($file, DateTime $d)
    {
        $diff = Storage::lastModified(Media::getDir() . $file) - $d->getTimestamp();
        $this->assertGreaterThan(0, $diff,
            "file '$file' was not updated recently ($diff)."
        );
    }
    private function assertOlder($file, DateTime $d)
    {
        $diff = Storage::lastModified(Media::getDir() . $file) - $d->getTimestamp();
        $this->assertLessThan(0, $diff,
            "file '$file' should not have been updated recently ($diff)."
        );
    }
    private function setupFilesMore()
    {
        $this->setupFiles();

        Artisan::call("media:create", [ 'file' => "../examplemedia/3/melody.ogg" ]);
        Artisan::call("media:create", [ 'file' => "../examplemedia/4/harmony.musicxml" ]);
        Artisan::call("media:create", [ 'file' => "../examplemedia/4/harmony.musicxml" ]);
        // Simulate there being an errant file by copying a ogg over a musicxml file.
        Storage::delete(Media::getDir() . "/4/harmony.musicxml");
        Storage::copy(Media::getDir() . "/3/original", Media::getDir() . "/4/harmony.musicxml");

        $this->assertExists("/3/original");
        $this->assertExists("/4/harmony.musicxml");
        $this->assertExists("/5/harmony.musicxml");
        $this->assertDatabaseHas("media", [
            'id' => 4,
            'originalFile' => 'harmony.musicxml',
        ]);

        // We have
        // 1 - a midi file
        // 2 - a musicxml file with the midi file already cached
        // 3 - an ogg file (can't currently be converted to midi)
        // 4 - a musicxml file that will cause errors when loaded
        // 5 - a musicxml file
    }
    private function sleep()
    {
        // Sleep for slightly more than a second (so the unix time always increments).
        usleep(1100000);
    }
}
