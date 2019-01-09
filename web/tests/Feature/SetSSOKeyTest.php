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
        $resource = openssl_pkey_new(["private_key_type" => OPENSSL_KEYTYPE_RSA]);
        $publicKey = openssl_pkey_get_details($resource)["key"];
        openssl_pkey_free($resource);

        $exitCode = Artisan::call("robo:set-sso-key", [ 'key' => $publicKey ]);
        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();

        $this->assertContains("SSO public key stored in ", $output);
        $this->assertContains('testing-sso-public.key', $output);
        $this->assertEquals($publicKey, Storage::get('testing-sso-public.key'));
    }
}
