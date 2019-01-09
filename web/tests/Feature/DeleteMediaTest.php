<?php

namespace Tests\Feature;

use Artisan;
use App\Media;
use Tests\ClearMedia;
use Tests\ClearDeleteMediaTrait;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class DeleteMediaTest extends TestCase
{
    use RefreshDatabase;
    use ClearMedia;
    use ClearDeleteMediaTrait;

    public function testDeleteUntracked()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:delete", [ 'media' => 'untracked' ]);
        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();

        $this->assertContains("Deleted 1 file.", $output);
        $this->assertContains("Deleted 1 directory.", $output);
        $this->assertContains("Deleted 0 media entries.", $output);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertNotDeleted("/1/harmony.musicxml");
        $this->assertNotDeleted("/2/harmony.midi");
        $this->assertDeleted("/3/harmony.musicxml");
        $this->assertNotNull(Media::find(1));
        $this->assertNotNull(Media::find(2));
        $this->assertNull(Media::find(3));
    }

    public function testDeleteOne()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:delete", [ 'media' => '1' ]);
        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();

        $this->assertContains("Deleted 2 files.", $output);
        $this->assertContains("Deleted 1 directory.", $output);
        $this->assertContains("Deleted 1 media entry.", $output);

        $this->assertDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertDeleted("/1/harmony.musicxml");
        $this->assertNotDeleted("/2/harmony.midi");
        $this->assertNotDeleted("/3/harmony.musicxml");
        $this->assertNull(Media::find(1));
        $this->assertNotNull(Media::find(2));
        $this->assertNull(Media::find(3));
    }

    public function testVerbose()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:delete", [ 'media' => 'untracked', '--verbose' => true ]);
        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();

        $this->assertContains("Deleted 1 file inside " . Media::getDir() . "/3", $output);
        $this->assertContains("Deleted 1 file.", $output);
        $this->assertContains("Deleted 1 directory.", $output);
        $this->assertContains("Deleted 0 media entries.", $output);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertNotDeleted("/1/harmony.musicxml");
        $this->assertNotDeleted("/2/harmony.midi");
        $this->assertDeleted("/3/harmony.musicxml");
        $this->assertNotNull(Media::find(1));
        $this->assertNotNull(Media::find(2));
        $this->assertNull(Media::find(3));
    }

    public function testDryRun()
    {
        $this->setupFiles();
        $exitCode = Artisan::call("media:delete", [ 'media' => '1', '--dry-run' => true ]);
        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();

        $this->assertContains("Would delete 2 files inside " . Media::getDir() . "/1", $output);
        $this->assertContains("Would delete media entry 1", $output);
        $this->assertContains("Would delete 2 files.", $output);
        $this->assertContains("Would delete 1 directory.", $output);
        $this->assertContains("Would delete 1 media entry.", $output);

        $this->assertNotDeleted("/1/harmony.midi");
        $this->assertNotDeleted("/2/harmony.musicxml");
        $this->assertNotDeleted("/1/harmony.musicxml");
        $this->assertNotDeleted("/2/harmony.midi");
        $this->assertNotDeleted("/3/harmony.musicxml");
        $this->assertNotNull(Media::find(1));
        $this->assertNotNull(Media::find(2));
        $this->assertNull(Media::find(3));
    }
}
