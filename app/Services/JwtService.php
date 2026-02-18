<?php

namespace App\Services;

use App\Models\User;
use DateTimeImmutable;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

final class JwtService
{
    private readonly JwtFacade $facade;

    private readonly Sha256 $signer;

    private readonly \Lcobucci\JWT\Signer\Key $key;

    public function __construct()
    {
        $this->facade = new JwtFacade();
        $this->signer = new Sha256();
        $secret = config('jwt.secret');
        if (empty($secret)) {
            throw new \RuntimeException('JWT secret is not set. Set JWT_SECRET in .env or run php artisan jwt:secret');
        }
        $this->key = InMemory::plainText($secret);
    }

    /**
     * Kullanıcı için JWT token oluşturur.
     */
    public function createToken(User $user): string
    {
        $ttlMinutes = config('jwt.ttl', 60);
        $issuer = config('jwt.issuer', 'kargomok');

        $token = $this->facade->issue(
            $this->signer,
            $this->key,
            static function (Builder $builder, DateTimeImmutable $issuedAt) use ($user, $ttlMinutes, $issuer): Builder {
                return $builder
                    ->issuedBy($issuer)
                    ->relatedTo((string) $user->id)
                    ->expiresAt($issuedAt->modify("+{$ttlMinutes} minutes"));
            }
        );

        return $token->toString();
    }

    /**
     * JWT string'ini parse eder ve doğrular. Geçerliyse token döner, değilse null.
     */
    public function parseAndValidate(string $jwt): ?UnencryptedToken
    {
        try {
            $clock = new class implements \Psr\Clock\ClockInterface {
                public function now(): DateTimeImmutable
                {
                    return new DateTimeImmutable();
                }
            };
            return $this->facade->parse(
                $jwt,
                new SignedWith($this->signer, $this->key),
                new LooseValidAt($clock)
            );
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Token'dan user id (sub claim) döner.
     */
    public function getUserIdFromToken(UnencryptedToken $token): ?int
    {
        $sub = $token->claims()->get('sub');
        if ($sub === null) {
            return null;
        }
        $id = (int) $sub;
        return $id > 0 ? $id : null;
    }
}
