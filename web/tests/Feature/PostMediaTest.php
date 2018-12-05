<?php

namespace Tests\Feature;

use app\Media;
use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Tests\ClearKeys;
use Tests\ClearMedia;
use Tests\TestCase;

class PostMediaTest extends TestCase
{
	use RefreshDatabase;
	use ClearMedia;
	use ClearKeys;

	public function testPostMediaNotSetup()
	{
		$file = new UploadedFile( '/var/www/examplemedia/2/melody.midi', 'filename.ext' );
		$response = $this->post( '/api/media', [ 'file' => $file ] );

		$response
			->assertStatus( 500 )
			->assertSee( "Trusted key not set up properly." );
	}

	public function testPostMediaUnAuthenticated()
	{
		$this->setupTestKeys();
		$file = new UploadedFile( '/var/www/examplemedia/2/melody.midi', 'filename.ext' );
		$response = $this->post( '/api/media', [ 'file' => $file ] );

		$response
			->assertStatus( 401 );
	}

	public function testPostMediaMangledJWT()
	{
		$this->setupTestKeys();
		$file = new UploadedFile( '/var/www/examplemedia/2/melody.midi', 'filename.ext' );
		$response = $this->post( '/api/media', [ 'file' => $file, 'jwt' => 'not a jwt string' ] );

		$response
			// TODO: Have this send a 401 error.
			//->assertStatus( 401 )
			->assertStatus( 500 );
	}

	public function testPostMediaUnTrustedUUID()
	{
		$this->setupTestKeys();
		$file = new UploadedFile( '/var/www/examplemedia/2/melody.midi', 'filename.ext' );
		$response = $this->post( '/api/media',
			[ 'file' => $file, 'jwt' => $this->getJWT( 'untrusted-uuid' ) ]
		);

		$response
			->assertStatus( 403 );
	}

	public function testPostMediaUnTrustedKey()
	{
		$this->setupTestKeys();
		$uuid = 'ab8bc5f3-3bc7-487d-a5bf-0a542caae79f';
		Artisan::call( "robo:trust-uuid", [ 'uuid' => $uuid ] );
		$jwt = $this->getJWT( $uuid );

		$this->clearKeys();
		// Generate a different public key than was used to sign the JWT.
		$this->setupTestKeys();

		$file = new UploadedFile( '/var/www/examplemedia/2/melody.midi', 'filename.ext' );
		$response = $this->post( '/api/media', [ 'file' => $file, 'jwt' => $jwt ] );

		$response
			->assertStatus( 401 );
	}

	public function testPostMedia()
	{
		$this->setupTestKeys();
		// Create trusted user
		$uuid = 'ab8bc5f3-3bc7-487d-a5bf-0a542caae79f';
		Artisan::call( "robo:trust-uuid", [ 'uuid' => $uuid ] );

		$file = new UploadedFile( '/var/www/examplemedia/2/melody.midi', 'filename.ext' );
		$response = $this->post( '/api/media', [ 'file' => $file, 'jwt' => $this->getJWT( $uuid ) ] );

		$response
			->assertJson( [ 'textID' => NULL, 'tuneID' => NULL ] )
			->assertJsonMissing( [ 'originalFile' => 'original' ] )
			->assertStatus( 201 );
		$media = Media::find( $response->json()['id'] );
		$response->assertJson( [ 'originalFile' => $media->originalFile ] );
	}

	public function testPostMediaWithIDs()
	{
		$this->setupTestKeys();
		// Create trusted user
		$uuid = 'ab8bc5f3-3bc7-487d-a5bf-0a542caae79f';
		Artisan::call( "robo:trust-uuid", [ 'uuid' => $uuid ] );

		$originalFile = '/var/www/examplemedia/1/melody.musicxml';
		$file = new UploadedFile( $originalFile, 'filename.ext' );
		$response = $this->post(
			'/api/media',
			[ 'file' => $file, 'textID' => 12345, 'tuneID' => 54321, 'jwt' => $this->getJWT( $uuid ) ]
		);

		$response
			->assertJson( [ 'textID' => 12345, 'tuneID' => 54321 ] )
			->assertJsonMissing( [ 'originalFile' => 'original' ] )
			->assertStatus( 201 );
		$media = Media::find( $response->json()['id'] );
		$response->assertJson( [ 'originalFile' => $media->originalFile ] );
		$this->assertEquals(
			file_get_contents( $originalFile ),
			file_get_contents( $media->getAbsPath( $media->originalFile ) )
		);
	}

	/**
	 * @brief Install a public key (and store the private key so we can use it for testing).
	 */
	private function setupTestKeys()
	{
		// Generate Key pair
		$privateKey = 'testing-sso-private.key';
		Storage::put( $privateKey, shell_exec( "openssl genrsa 2>/dev/null" ) );
		$privatePath = storage_path( "app/$privateKey" );

		$publicKeyContents = shell_exec( "(cat $privatePath | openssl rsa -pubout)2>/dev/null" );
		$exitCode = Artisan::call( "robo:set-sso-key", [ 'key' => $publicKeyContents ] );
	}

	/**
	 * @brief returns a JWT one can use for posting files.
	 * @param $uuid The UUID who this should be coming from.
	 */
	private function getJWT( $uuid )
	{
		$privatePath = storage_path( "app/testing-sso-private.key" );
		// Create JWT.
		$keychain = new Keychain();
		$builder = new Builder();
		return $builder
			->setIssuer( config( "app.url" ) )
			->setIssuedAt( time() )
			->setExpiration( time() + ( 60 * 3 ) )
			->set( 'action', 'prove_identity' )
			->set( 'uuid', $uuid )
			->sign(
				new Sha256(),
				$keychain->getPrivateKey( 'file://' . $privatePath )
			)
			->getToken();
	}
}
