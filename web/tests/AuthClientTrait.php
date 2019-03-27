<?php
namespace Tests;

use Artisan;
use Illuminate\Support\Facades\Storage;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha256;

trait AuthClientTrait
{
    /**
     * @brief Install a public key (and store the private key so we can use it for testing).
     */
    private function setupTestKeys()
    {
        // Generate Key pair
        $resource = openssl_pkey_new(["private_key_type" => OPENSSL_KEYTYPE_RSA]);
        $publicKeyContents = openssl_pkey_get_details($resource)["key"];
        $privateKey = 'testing-sso-private.key';
        $privateKeyContents = '';
        $this->assertTrue(openssl_pkey_export($resource, $privateKeyContents));
        openssl_pkey_free($resource);

        Storage::put($privateKey, $privateKeyContents);
        Artisan::call("robo:set-sso-key", [ 'key' => $publicKeyContents ]);
    }

    /**
     * @brief returns a JWT one can use for posting files.
     * @param $uuid The UUID who this should be coming from.
     */
    private function getJWT($uuid)
    {
        $privatePath = storage_path("app/testing-sso-private.key");
        // Create JWT.
        $keychain = new Keychain();
        $builder = new Builder();
        return $builder
            ->setIssuer(config("app.url"))
            ->setIssuedAt(time())
            ->setExpiration(time() + ( 60 * 3 ))
            ->set('action', 'prove_identity')
            ->set('uuid', $uuid)
            ->sign(
                new Sha256(),
                $keychain->getPrivateKey('file://' . $privatePath)
            )
            ->getToken();
    }
}
