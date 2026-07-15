<?php

namespace App\Auth;

use App\Models\AdminUser;
use App\Services\VerdanttApiClient;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Cache;

class ApiUserProvider implements UserProvider
{
    protected const CACHE_PREFIX = 'admin_auth_user_';

    public function __construct(protected VerdanttApiClient $api)
    {
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $data = Cache::get(self::CACHE_PREFIX . $identifier);

        return $data ? AdminUser::fromCacheArray($data) : null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        //
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            return null;
        }

        $response = $this->api->login($credentials['email'], $credentials['password']);

        if (! $response->successful() || ! $response->json('success')) {
            return null;
        }

        $token = $response->json('data.token');
        $userPayload = $response->json('data.user');

        if (! $token || ! $userPayload) {
            return null;
        }

        $user = AdminUser::fromApiPayload($userPayload, $token);

        Cache::put(self::CACHE_PREFIX . $user->id, $user->toCacheArray(), $this->tokenTtl($token));

        return $user;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $user instanceof AdminUser;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        //
    }

    /**
     * Orchid's LoginController expects an Eloquent-style provider so it can
     * look up the "remember this device" lock-screen user by id. We have no
     * local user table, so this always reports no cached lock-screen user.
     */
    public function createModel(): object
    {
        return new class {
            public function find($id): null
            {
                return null;
            }
        };
    }

    protected function tokenTtl(string $token): \DateTimeInterface
    {
        $parts = explode('.', $token);

        if (count($parts) === 3) {
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

            if (isset($payload['exp'])) {
                return \DateTimeImmutable::createFromFormat('U', (string) $payload['exp']);
            }
        }

        return now()->addDay();
    }
}
