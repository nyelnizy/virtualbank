<?php


namespace App\Services\V1;


use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Ramsey\Uuid\Uuid;

class TokenService
{
    public function generateToken(array $user,int $expiry_hours): string{
        $config = $this->getJwtConfig();
        assert($config instanceof Configuration);
        $now = new DateTimeImmutable();

        return $config->builder()
            ->issuedBy(config('app.url'))
            ->permittedFor('*')
            ->identifiedBy("token".$user["id"])
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+$expiry_hours hour"))
            ->withClaim('uid', $user["id"])
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }
    private function getJwtConfig(): Configuration
    {
        return Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512) and EdDSA over Curve25519
            new Sha256(),
            InMemory::file(base_path('keys/tym.pem')),
            InMemory::empty()
        );
    }
}
