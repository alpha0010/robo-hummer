<?php

namespace Tests\Feature;

use App\TrustedUUID;
use Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrustUUIDTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * A basic test example.
	 *
	 * @return void
	 */
	public function testTrustUUID()
	{
		$uuid = "ab8bc5f3-3bc7-487d-a5bf-0a542caae79f";
		$exitCode = Artisan::call(
			"robo:trust-uuid",
			[ 'uuid' => $uuid ]
		);
		$this->assertEquals( 0, $exitCode );
		$this->assertDatabaseHas( "trusted_uuids",
			[
				"id" => 1,
				"uuid" => $uuid,
			]
		);
		$this->assertTrue(true);
	}
}
