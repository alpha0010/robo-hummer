<?php

namespace Tests\Feature;

use Artisan;
use App\Media;
use Tests\ClearMedia;
use Tests\ClearDeleteMediaTrait;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ClearCachedMediaTest extends TestCase
{
    use RefreshDatabase;
    use ClearMedia;
    use ClearDeleteMediaTrait;

    public function testClearAllCached()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:clear-cache");
        $this->assertEquals(0, $exitCode);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertDeleted("/1/harmony.musicxml");
        $this->assertDeleted("/2/harmony.midi");
        $this->assertNotDeleted("/3/harmony.musicxml");
    }

    public function testClearType()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:clear-cache", ['--type' => "harmony.musicxml" ]);
        $this->assertEquals(0, $exitCode);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertDeleted("/1/harmony.musicxml");
        $this->assertNotDeleted("/2/harmony.midi");
        $this->assertNotDeleted("/3/harmony.musicxml");
    }

    public function testClearForOne()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:clear-cache", [ 'media' => "2" ]);
        $this->assertEquals(0, $exitCode);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertNotDeleted("/1/harmony.musicxml");
        $this->assertDeleted("/2/harmony.midi");
        $this->assertNotDeleted("/3/harmony.musicxml");
    }
    public function testClearForOneFailure()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:clear-cache", [ 'media' => "3" ]);
        $this->assertEquals(1, $exitCode);
        $output = Artisan::output();
        $this->assertContains("Could not find media entry", $output);
        $this->assertContains("media:delete untracked", $output);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertNotDeleted("/1/harmony.musicxml");
        $this->assertNotDeleted("/2/harmony.midi");
        $this->assertNotDeleted("/3/harmony.musicxml");
    }
    public function testClearIDandType()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:clear-cache", ['media' => "1", '--type' => "harmony.musicxml"]);
        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();

        //$this->assertContains("Deleted 1 file", $output);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertDeleted("/1/harmony.musicxml");
        $this->assertNotDeleted("/2/harmony.midi");
        $this->assertNotDeleted("/3/harmony.musicxml");
    }
    public function testClearIDandTypeFailure()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:clear-cache", ['media' => "2", '--type' => "harmony.musicxml"]);
        $this->assertEquals(1, $exitCode);
        $output = Artisan::output();

        $this->assertContains("Could not find media entry", $output);
        $this->assertContains("The media ID is '2'", $output);
        $this->assertContains("The original file is not 'harmony.musicxml'", $output);
        $this->assertContains("media:delete untracked", $output);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertNotDeleted("/1/harmony.musicxml");
        $this->assertNotDeleted("/2/harmony.midi");
        $this->assertNotDeleted("/3/harmony.musicxml");
    }
}
