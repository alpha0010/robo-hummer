<?php

namespace Tests\Feature;

use app\Media;
use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\AuthClientTrait;
use Tests\ClearKeys;
use Tests\ClearMedia;
use Tests\TestCase;

class PatchMediaTest extends TestCase
{
    use AuthClientTrait;
    use ClearKeys;
    use ClearMedia;
    use RefreshDatabase;

    public function testPatchMedia()
    {
        $this->setupTestKeys();
        // Create trusted user
        $uuid = 'ab8bc5f3-3bc7-487d-a5bf-0a542caae79f';
        Artisan::call("robo:trust-uuid", [ 'uuid' => $uuid ]);

        $file = new UploadedFile('/var/www/examplemedia/2/melody.midi', 'filename.ext');
        $response = $this->post('/api/media', [ 'file' => $file, 'jwt' => $this->getJWT($uuid) ]);

        $response
            ->assertJson([ 'textID' => null, 'tuneID' => null ])
            ->assertJsonMissing([ 'originalFile' => 'original' ])
            ->assertStatus(201);
        $media = Media::find($response->json()['id']);
        $response->assertJson([ 'originalFile' => $media->originalFile ]);

        $response = $this->patch('/api/media/1', ['shouldIndex' => 1, 'jwt' => $this->getJWT($uuid)]);
        $response->assertJson(['shouldIndex' => 1]);
        $media = Media::find($response->json()['id']);
        $this->assertEquals(1, $media->shouldIndex);

        $response = $this->patch('/api/media/1', ['shouldIndex' => 0, 'jwt' => $this->getJWT($uuid)]);
        $response->assertJson(['shouldIndex' => 0]);
        $media = Media::find($response->json()['id']);
        $this->assertEquals(0, $media->shouldIndex);
    }
}
