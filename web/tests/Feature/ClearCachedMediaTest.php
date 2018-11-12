<?php

namespace Tests\Feature;

use Artisan;
use App\Media;
use Tests\ClearMedia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ClearCachedMediaTest extends TestCase
{
	use RefreshDatabase;
	use ClearMedia;

	private function setupFiles()
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
	private function assertDeleted( $file )
	{
		$this->assertFalse(
			Storage::exists( Media::getDir() . $file ),
			"File '$file' was not deleted."
		);
	}
	private function assertNotDeleted( $file )
	{
		$this->assertTrue(
			Storage::exists( Media::getDir() . $file ),
			"File '$file' should not have been deleted."
		);
	}

	public function testClearAllCached()
	{
		$this->setupFiles();
		$exitCode = Artisan::call( "media:clear-cache" );
		$this->assertEquals( 0, $exitCode );

		$this->assertNotDeleted( "/1/harmony.midi" );
		$this->assertNotDeleted( "/2/harmony.musicxml" );
		$this->assertDeleted( "/1/harmony.musicxml" );
		$this->assertDeleted( "/2/harmony.midi" );
		$this->assertNotDeleted( "/3/harmony.musicxml" );
	}

	public function testClearType()
	{
		$this->setupFiles();
		$exitCode = Artisan::call( "media:clear-cache", ['--type' => "harmony.musicxml" ] );
		$this->assertEquals( 0, $exitCode );

		$this->assertNotDeleted( "/1/harmony.midi" );
		$this->assertNotDeleted( "/2/harmony.musicxml" );
		$this->assertDeleted( "/1/harmony.musicxml" );
		$this->assertNotDeleted( "/2/harmony.midi" );
		$this->assertNotDeleted( "/3/harmony.musicxml" );
	}

	public function testClearForOne()
	{
		$this->setupFiles();
		$exitCode = Artisan::call( "media:clear-cache", [ 'media' => "2" ] );
		$this->assertEquals( 0, $exitCode );

		$this->assertNotDeleted( "/1/harmony.midi" );
		$this->assertNotDeleted( "/2/harmony.musicxml" );
		$this->assertNotDeleted( "/1/harmony.musicxml" );
		$this->assertDeleted( "/2/harmony.midi" );
		$this->assertNotDeleted( "/3/harmony.musicxml" );
	}
	public function testClearForOneFailure()
	{
		$this->setupFiles();
		$exitCode = Artisan::call( "media:clear-cache", [ 'media' => "3" ] );
		$this->assertEquals( 1, $exitCode );
		$output = Artisan::output();
		$this->assertContains( "Could not find media file '3'", $output );
		$this->assertContains( "media:clear-cache --force", $output );

		$this->assertNotDeleted( "/1/harmony.midi" );
		$this->assertNotDeleted( "/2/harmony.musicxml" );
		$this->assertNotDeleted( "/1/harmony.musicxml" );
		$this->assertNotDeleted( "/2/harmony.midi" );
		$this->assertNotDeleted( "/3/harmony.musicxml" );
	}

	public function testClearForce()
	{
		$this->setupFiles();
		$exitCode = Artisan::call( "media:clear-cache", [ '--force' => TRUE ] );
		$this->assertEquals( 0, $exitCode );

		$this->assertNotDeleted( "/1/harmony.midi" );
		$this->assertNotDeleted( "/2/harmony.musicxml" );
		$this->assertDeleted( "/1/harmony.musicxml" );
		$this->assertDeleted( "/2/harmony.midi" );
		$this->assertDeleted( "/3/harmony.musicxml" );
	}
}
