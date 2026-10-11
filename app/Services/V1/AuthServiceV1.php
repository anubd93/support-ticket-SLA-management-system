<?php

namespace App\Services\V1;

use App\Repositories\V1\RoleRepositoryV1;
use App\Repositories\V1\UserRepositoryV1;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthServiceV1
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function __construct(private readonly UserRepositoryV1 $userRepositoryV1,
                                private readonly RoleRepositoryV1 $roleRepositoryV1
    ) {}

    public function login(array $data, string $ip): bool|string
    {
        $throttleKey = Str::transliterate(Str::lower($data['email'])) . '|' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return "Too many login attempts. Please try again in {$seconds} seconds.";
        }

        $credentials = ['email' => $data['email'], 'password' => $data['password']];

        if (! Auth::guard('web')->attempt($credentials, (bool) ($data['remember'] ?? false))) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return 'These credentials do not match our records.';
        }

        if (! Auth::guard('web')->user()->is_active) {
            Auth::guard('web')->logout();

            return 'Your account has been deactivated. Please contact support.';
        }

        RateLimiter::clear($throttleKey);

        return true;
    }
}
