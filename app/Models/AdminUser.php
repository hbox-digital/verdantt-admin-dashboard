<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Orchid\Screen\Contracts\Personable;
use Orchid\Support\Presenter;

class AdminUser implements Authenticatable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        public string $apiToken,
        public ?string $profileImage = null,
    ) {
    }

    public static function fromApiPayload(array $user, string $token): self
    {
        return new self(
            id: (int) $user['id'],
            name: trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['email'],
            email: $user['email'],
            role: $user['role'] ?? 'admin',
            apiToken: $token,
            profileImage: $user['profile_image'] ?? null,
        );
    }

    public function toCacheArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'apiToken' => $this->apiToken,
            'profileImage' => $this->profileImage,
        ];
    }

    public static function fromCacheArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
            role: $data['role'],
            apiToken: $data['apiToken'],
            profileImage: $data['profileImage'] ?? null,
        );
    }

    /**
     * Every user retrieved through the remote API's admin login already
     * passed that check server-side — there is no local permission system,
     * so any authenticated admin has full access to every screen.
     */
    public function hasAccess(string $permit, bool $cache = true): bool
    {
        return true;
    }

    public function hasAnyAccess($permissions, bool $cache = true): bool
    {
        return true;
    }

    public function presenter(): Presenter
    {
        return new class($this) extends Presenter implements Personable {
            public function title(): string
            {
                return $this->entity->name;
            }

            public function subTitle(): string
            {
                return ucfirst($this->entity->role);
            }

            public function url(): string
            {
                return route('platform.profile');
            }

            public function image(): ?string
            {
                return $this->entity->profileImage
                    ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->entity->name) . '&color=000000&background=C9D9AE';
            }
        };
    }

    public function getKey(): int
    {
        return $this->id;
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->email;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): int
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        //
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
