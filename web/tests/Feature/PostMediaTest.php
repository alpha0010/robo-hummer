<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\ClearMedia;
use Tests\TestCase;

class PostMediaTest extends TestCase
{
	use RefreshDatabase;
	use ClearMedia;

	public function testPostMedia()
	{
		$file = new UploadedFile( '/var/www/examplemedia/2/melody.midi', 'filename.ext' );
		$response = $this->post( '/api/media', [ 'file' => $file ] );

		$response
			->assertJson( [ 'id' => 1, 'textID' => NULL, 'tuneID' => NULL ] )
			->assertJsonMissing( [ 'originalFile' => 'original' ] )
			->assertStatus( 201 );
	}

	public function testPostMediaWithIDs()
	{
		$file = new UploadedFile( '/var/www/examplemedia/1/melody.musicxml', 'filename.ext' );
		$response = $this->post(
			'/api/media',
			[ 'file' => $file, 'textID' => 12345, 'tuneID' => 54321 ]
		);

		$response
			->assertJson( [ 'id' => 1, 'textID' => 12345, 'tuneID' => 54321 ] )
			->assertJsonMissing( [ 'originalFile' => 'original' ] )
			->assertStatus( 201 );
	}
}
