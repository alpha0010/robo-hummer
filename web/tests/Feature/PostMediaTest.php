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

class PostMediaTest extends TestCase
{
    use AuthClientTrait;
    use ClearKeys;
    use ClearMedia;
    use RefreshDatabase;

    public function testPostMediaNotSetup()
    {
        $file = new UploadedFile('/var/www/examplemedia/2/melody.midi', 'filename.ext');
        $response = $this->post('/api/media', [ 'file' => $file ]);

        $response
            ->assertStatus(500)
            ->assertSee("Trusted key not set up properly.");
    }

    public function testPostMediaUnAuthenticated()
    {
        $this->setupTestKeys();
        $file = new UploadedFile('/var/www/examplemedia/2/melody.midi', 'filename.ext');
        $response = $this->post('/api/media', [ 'file' => $file ]);

        $response
            ->assertStatus(401);
    }

    public function testPostMediaMangledJWT()
    {
        $this->setupTestKeys();
        $file = new UploadedFile('/var/www/examplemedia/2/melody.midi', 'filename.ext');
        $response = $this->post('/api/media', [ 'file' => $file, 'jwt' => 'not a jwt string' ]);

        $response
            // TODO: Have this send a 401 error.
            //->assertStatus( 401 )
            ->assertStatus(500);
    }

    public function testPostMediaUnTrustedUUID()
    {
        $this->setupTestKeys();
        $file = new UploadedFile('/var/www/examplemedia/2/melody.midi', 'filename.ext');
        $response = $this->post(
            '/api/media',
            [ 'file' => $file, 'jwt' => $this->getJWT('untrusted-uuid') ]
        );

        $response
            ->assertStatus(403);
    }

    public function testPostMediaUnTrustedKey()
    {
        $this->setupTestKeys();
        $uuid = 'ab8bc5f3-3bc7-487d-a5bf-0a542caae79f';
        Artisan::call("robo:trust-uuid", [ 'uuid' => $uuid ]);
        $jwt = $this->getJWT($uuid);

        $this->clearKeys();
        // Generate a different public key than was used to sign the JWT.
        $this->setupTestKeys();

        $file = new UploadedFile('/var/www/examplemedia/2/melody.midi', 'filename.ext');
        $response = $this->post('/api/media', [ 'file' => $file, 'jwt' => $jwt ]);

        $response
            ->assertStatus(401);
    }

    public function testPostMedia()
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
    }

    public function testPostMediaWithIDs()
    {
        $this->setupTestKeys();
        // Create trusted user
        $uuid = 'ab8bc5f3-3bc7-487d-a5bf-0a542caae79f';
        Artisan::call("robo:trust-uuid", [ 'uuid' => $uuid ]);

        $originalFile = '/var/www/examplemedia/1/melody.musicxml';
        $file = new UploadedFile($originalFile, 'filename.ext');
        $response = $this->post(
            '/api/media',
            [ 'file' => $file, 'textID' => 12345, 'tuneID' => 54321, 'jwt' => $this->getJWT($uuid) ]
        );

        $response
            ->assertJson([ 'textID' => 12345, 'tuneID' => 54321 ])
            ->assertJsonMissing([ 'originalFile' => 'original' ])
            ->assertStatus(201);
        $media = Media::find($response->json()['id']);
        $response->assertJson([ 'originalFile' => $media->originalFile ]);
        $this->assertEquals(
            file_get_contents($originalFile),
            file_get_contents($media->getAbsPath($media->originalFile))
        );
    }
}
