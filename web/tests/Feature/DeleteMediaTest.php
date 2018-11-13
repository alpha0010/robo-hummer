<?php

namespace Tests\Feature;

use Artisan;
use App\Media;
use Tests\ClearMedia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class DeleteMediaTest extends TestCase
{
	use RefreshDatabase;
	use ClearMedia;
	use ClearDeleteMediaTrait;

	public function testClearForce()
	{
		$this->setupFiles();
		$exitCode = Artisan::call( "media:delete", [ 'media' => 'untracked' ] );
		$this->assertEquals( 0, $exitCode );

		$this->assertNotDeleted( "/1/harmony.midi" );
		$this->assertNotDeleted( "/2/harmony.musicxml" );
		$this->assertNotDeleted( "/1/harmony.musicxml" );
		$this->assertNotDeleted( "/2/harmony.midi" );
		$this->assertDeleted( "/3/harmony.musicxml" );
	}
}
