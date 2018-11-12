<?php

namespace Tests\Feature;

use Artisan;
use App\Media;
use Tests\ClearMedia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class CreateMediaTest extends TestCase
{
	use RefreshDatabase;
	use ClearMedia;

	public function testCreateMediaFromLocal()
	{
		$localPath = "../examplemedia/1/melody.musicxml";
		$exitCode = Artisan::call(
			"media:create",
			[ 'file' => $localPath ]
		);
		$this->assertEquals( 0, $exitCode );
		$this->assertDatabaseHas( "media",
			[
				"id" => 1,
				"originalFile" => "harmony.musicxml",
			]
		);
		$this->assertEquals(
			Storage::get( Media::getDir() . "/1/harmony.musicxml" ),
			file_get_contents( $localPath )
		);

		// TODO: Test that the output of the command produced a link to the file,
		// and that that link gives the correct file.
	}

	public function testMediaTypeDetermined()
	{
		$localPath = "../examplemedia/2/melody.midi";
		$exitCode = Artisan::call(
			"media:create",
			[ 'file' => $localPath ]
		);
		$this->assertEquals( 0, $exitCode );
		$output = Artisan::output();
		$this->assertContains( "1/harmony.midi", $output );

		$this->assertDatabaseHas( "media",
			[
				"id" => 1,
				"originalFile" => "harmony.midi",
			]
		);
	}

	public function testMediaTypeUndetermined()
	{
		$localPath = "../examplemedia/3/melody.ogg";
		$exitCode = Artisan::call(
			"media:create",
			[ 'file' => $localPath ]
		);
		$this->assertEquals( 0, $exitCode );
		$output = Artisan::output();
		$this->assertContains( "Unable to determine media type.", $output );

		$this->assertDatabaseHas( "media",
			[
				"id" => 1,
				"originalFile" => "original",
			]
		);
	}
}
