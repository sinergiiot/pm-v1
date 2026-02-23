<?php

namespace App\Listeners;

use DutchCodingCompany\FilamentSocialite\Events\Login;

class UpdateSocialiteUserTokens
{
    /**
     * When a user logs in via Google (or other OAuth), update stored tokens
     * so we can call Google Calendar API on their behalf.
     */
    public function handle(Login $event): void
    {
        $event->socialiteUser->fillTokensFromOAuthUser($event->oauthUser);
        $event->socialiteUser->save();
    }
}
