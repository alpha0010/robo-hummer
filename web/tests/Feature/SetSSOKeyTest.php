<?php

namespace Tests\Feature;

use Artisan;
use App\Media;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class SetSSOKeyTest extends TestCase
{
	public function testKeyAsArg()
	{
		$publicKey = "cheese";
		$exitCode = Artisan::call( "robo:set-sso-key", [ 'key' => $publicKey ] );
		$this->assertEquals( 0, $exitCode );
		$output = Artisan::output();

		$this->assertContains( "SSO public key stored in ", $output );
		$this->assertContains( 'testing-sso-public.key', $output );
		$this->assertEquals( $publicKey, Storage::get( 'testing-sso-public.key' ) );
	}
}
