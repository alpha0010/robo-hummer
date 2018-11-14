<?php

namespace Tests\Feature;

use app\Media;
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
			->assertJson( [ 'textID' => NULL, 'tuneID' => NULL ] )
			->assertJsonMissing( [ 'originalFile' => 'original' ] )
			->assertStatus( 201 );
		$media = Media::find( $response->json()['id'] );
		$response->assertJson( [ 'originalFile' => $media->originalFile ] );
	}

	public function testPostMediaWithIDs()
	{
		$file = new UploadedFile( '/var/www/examplemedia/1/melody.musicxml', 'filename.ext' );
		$response = $this->post(
			'/api/media',
			[ 'file' => $file, 'textID' => 12345, 'tuneID' => 54321 ]
		);

		$response
			->assertJson( [ 'textID' => 12345, 'tuneID' => 54321 ] )
			->assertJsonMissing( [ 'originalFile' => 'original' ] )
			->assertStatus( 201 );
		$media = Media::find( $response->json()['id'] );
		$response->assertJson( [ 'originalFile' => $media->originalFile ] );
		// TODO: Test that the file was saved in the correct location.
	}
}
