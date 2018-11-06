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
	/**
	 * A basic test example.
	 *
	 * @return void
	 */
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
				"originalFile" => "melody.musicxml"
			]
		);
		$this->assertEquals(
			Storage::get( Media::getDir() . "/1/melody.musicxml" ),
			file_get_contents( $localPath )
		);

		// TODO: Test that the output of the command produced a link to the file,
		// and that that link gives the correct file.
	}
}
