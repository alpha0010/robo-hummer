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
		$originalFile = '/var/www/examplemedia/1/melody.musicxml';
		$file = new UploadedFile( $originalFile, 'filename.ext' );
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
		$this->assertEquals(
			file_get_contents( $originalFile ),
			file_get_contents( '/var/www/web/storage/app/' . $media->getPath() . $media->originalFile )
		);
	}
}
