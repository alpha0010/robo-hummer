<?php

namespace Tests\Feature;

use Artisan;
use Tests\ClearMedia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
		$exitCode = Artisan::call(
			"media:create",
			[ 'file' => "../examplemedia/1/melody.musicxml" ]
		);
		$this->assertEquals( 0, $exitCode );
		$this->assertDatabaseHas( "media",
			[
				"originalFile" => "melody.musicxml"
			]
		);
	}
}
