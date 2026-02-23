<?php

namespace App\Models;

use DutchCodingCompany\FilamentSocialite\Models\Contracts\FilamentSocialiteUser as FilamentSocialiteUserContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Two\User as SocialiteTwoUser;

class SocialiteUser extends Model implements FilamentSocialiteUserContract
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUser(): Authenticatable
    {
        return $this->user;
    }

    public static function findForProvider(string $provider, SocialiteUserContract $oauthUser): ?self
    {
        return self::where('provider', $provider)
            ->where('provider_id', $oauthUser->getId())
            ->first();
    }

    public static function createForProvider(
        string $provider,
        SocialiteUserContract $oauthUser,
        Authenticatable $user
    ): self {
        $socialiteUser = self::create([
            'user_id' => $user->getAuthIdentifier(),
            'provider' => $provider,
            'provider_id' => $oauthUser->getId(),
        ]);

        $socialiteUser->fillTokensFromOAuthUser($oauthUser);
        $socialiteUser->save();

        return $socialiteUser;
    }

    /**
     * Update stored tokens from OAuth user (e.g. on login or connect).
     * Only applies when the provider returns Laravel\Socialite\Two\User (e.g. Google).
     */
    public function fillTokensFromOAuthUser(SocialiteUserContract $oauthUser): void
    {
        if (! $oauthUser instanceof SocialiteTwoUser) {
            return;
        }

        $this->access_token = $oauthUser->token ?? null;
        $this->refresh_token = $oauthUser->refreshToken ?? $this->refresh_token;
        $this->token_expires_at = isset($oauthUser->expiresIn)
            ? now()->addSeconds($oauthUser->expiresIn)
            : null;
    }
}
