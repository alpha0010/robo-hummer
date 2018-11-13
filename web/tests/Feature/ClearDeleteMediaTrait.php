<?php

namespace Tests\Feature;

use Artisan;
use App\Media;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\ClearDeleteMediaTrait;

trait ClearDeleteMediaTrait
{
	function setupFiles()
	{
		$musicxmlFile = "../examplemedia/1/melody.musicxml";
		$midiFile = "../examplemedia/2/melody.midi";
		Artisan::call( "media:create", [ 'file' => $midiFile ] );
		Artisan::call( "media:create", [ 'file' => $musicxmlFile ] );

		// Simulate cached files being created by copying files.
		Storage::copy( Media::getDir() . "/2/harmony.musicxml", Media::getDir() . "/1/harmony.musicxml" );
		Storage::copy( Media::getDir() . "/1/harmony.midi", Media::getDir() . "/2/harmony.midi" );
		Storage::copy( Media::getDir() . "/2/harmony.musicxml", Media::getDir() . "/3/harmony.musicxml" );

		$this->assertEquals( "harmony.midi", Media::find(1)->originalFile );
		$this->assertEquals( "harmony.musicxml", Media::find(2)->originalFile );
		// TODO: assert that media 3 is not in the database.

		$this->assertTrue( Storage::exists( Media::getDir() . "/1/harmony.midi" ) );
		$this->assertTrue( Storage::exists( Media::getDir() . "/2/harmony.musicxml" ) );
		$this->assertTrue( Storage::exists( Media::getDir() . "/1/harmony.musicxml" ) );
		$this->assertTrue( Storage::exists( Media::getDir() . "/2/harmony.midi" ) );
		$this->assertTrue( Storage::exists( Media::getDir() . "/3/harmony.musicxml" ) );
	}

	function assertDeleted( $file )
	{
		$this->assertFalse(
			Storage::exists( Media::getDir() . $file ),
			"File '$file' was not deleted."
		);
	}
	function assertNotDeleted( $file )
	{
		$this->assertTrue(
			Storage::exists( Media::getDir() . $file ),
			"File '$file' should not have been deleted."
		);
	}
}
