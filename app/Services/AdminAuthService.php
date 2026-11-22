<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

class AdminAuthService
{
    public const COOKIE = 'staza_admin';

    public const LIFETIME = 30;

    public const SESSION_KEY = 'admin_jwt_id';

    /**
     * Validate administrator credentials and issue a token tied to the login ID.
     *
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws ValidationException
     */
    public function login(array $credentials, string $loginId): string
    {
        $guard = Auth::guard('web');
        if (! $guard->validate($credentials + ['role' => Role::Admin->value])) {
            throw ValidationException::withMessages(['email' => 'Email adresa ili lozinka nisu ispravni.']);
        }

        $now = time();

        return JWT::encode([
            'aud' => 'staza-admin',
            'sub' => (string) $guard->getLastAttempted()->getAuthIdentifier(),
            'jti' => $loginId,
            'iat' => $now,
            'exp' => $now + self::LIFETIME * 60,
        ], $this->key(), 'HS256');
    }

    /**
     * Verify the token against the login ID and resolve the current administrator.
     */
    public function resolve(?string $token, ?string $loginId): ?User
    {
        if (! $token || ! $loginId) {
            return null;
        }

        try {
            $claims = JWT::decode($token, new Key($this->key(), 'HS256'));
        } catch (UnexpectedValueException|DomainException) {
            return null;
        }

        if (($claims->aud ?? null) !== 'staza-admin'
            || ! isset($claims->exp, $claims->sub, $claims->jti)
            || ! is_string($claims->sub) || ! ctype_digit($claims->sub)
            || ! is_string($claims->jti) || ! hash_equals($loginId, $claims->jti)) {
            return null;
        }

        return User::query()->where('role', Role::Admin->value)->find($claims->sub);
    }

    /**
     * Derive the signing key for administrator tokens from the application encryption key.
     */
    private function key(): string
    {
        return hash_hkdf('sha256', app('encrypter')->getKey(), 32, 'staza.admin.jwt');
    }
}
