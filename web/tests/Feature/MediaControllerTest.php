<?php

namespace Tests\Feature;

use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ClearMedia;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
	use RefreshDatabase;
	use ClearMedia;

	public function testPlain404()
	{
		$response = $this->get( '/media/12345/non-existent.file' );
		$response->assertStatus( 404 );
	}
	public function testMedia404()
	{
		$localPath = "../examplemedia/2/melody.midi";
		Artisan::call( "media:create", [ 'file' => $localPath ] );

		$this->assertDatabaseHas( 'media', [ 'id' => 1 ] );

		$response = $this->get( '/media/1/non-existent.file' );
		$response->assertStatus( 404 );
	}

	public function testOriginalUndetected()
	{
		// TODO: After we can detect audio, change this test.
		$localPath = "../examplemedia/3/melody.ogg";
		Artisan::call( "media:create", [ 'file' => $localPath ] );

		$this->assertDatabaseHas( 'media', [ 'id' => 1, 'originalFile' => 'original' ] );

		$response = $this->get( '/media/1/original' );
		$response->assertOk();
	}

	public function testOriginalRedirect()
	{
		$localPath = "../examplemedia/2/melody.midi";
		Artisan::call( "media:create", [ 'file' => $localPath ] );

		$this->assertDatabaseMissing( 'media', [ 'originalFile' => 'original' ] );

		$response = $this->get( '/media/1/original' );
		$response->assertStatus( 302 );
		// TODO: Change this test after we can detect harmony vs melody uploads.
		$response->assertRedirect( '/media/1/harmony.midi' );
	}

	public function testMP3FromMidi()
	{
		$localPath = "../examplemedia/2/melody.midi";
		Artisan::call( "media:create", [ 'file' => $localPath ] );

		$response = $this->get( '/media/1/harmony.mp3' );
		$response->assertOk();
	}

	public function testCantGetIncipit()
	{
		$localPath = "../examplemedia/3/melody.ogg";
		Artisan::call( "media:create", [ 'file' => $localPath ] );

		// TODO: Change when we have a way to generate incipit from an audio file.
		$response = $this->get( '/media/1/incipit.json' );
		$response->assertStatus( 500 );
	}
}
