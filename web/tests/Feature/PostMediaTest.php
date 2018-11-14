<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ClearMedia;
use Tests\TestCase;

class PostMediaTest extends TestCase
{
	use RefreshDatabase;
	use ClearMedia;

	public function testPostMedia()
	{
		$file = new \Illuminate\Http\UploadedFile('/var/www/examplemedia/2/melody.midi', 'Cylinder.stl');
		$data = [
			'file' => $file,
		];
		$response = $this->post( '/api/media', $data );

		$response
			->assertJson( [ 'id' => 1 ] )
			->assertStatus( 201 );
	}
}
