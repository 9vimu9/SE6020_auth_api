<?php

namespace App\Services\MicroServiceToken;

use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use PHPOpenSourceSaver\JWTAuth\Exceptions\UserNotDefinedException;

class TokenIssuer
{
    /**
     * @throws Exception
     */
    public function issue(): string
    {
        if (!$user = auth()->user()) {
            throw new UserNotDefinedException();
        }
        $tokenBuilder = (new Builder(new JoseEncoder(), ChainedFormatter::default()));
        $algorithm = new Sha256();
        $signingKey = InMemory::plainText(config('microservice_token.secret'));

        $now = new DateTimeImmutable();
        $token = $tokenBuilder
            ->issuedBy(config('app.url'))
            ->identifiedBy(md5(mt_rand() . time()))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify('+0 second'))
            ->expiresAt($now->modify('+' . config('microservice_token.ttl') . ' minute'))
            ->withClaim('user_id', $user->id)
            ->withClaim('role', $user->role)
            ->getToken($algorithm, $signingKey);

        return $token->toString();
    }

}
