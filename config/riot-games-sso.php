<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Riot Games Client ID
    |--------------------------------------------------------------------------
    |
    | Your Riot Games OAuth client ID. You can obtain this from the
    | Riot Games Developer Portal.
    |
    */

    'client_id' => env('RIOT_GAMES_CLIENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Riot Games Client Secret
    |--------------------------------------------------------------------------
    |
    | Your Riot Games OAuth client secret (JWT assertion). You can obtain
    | this from the Riot Games Developer Portal.
    |
    */

    'client_secret' => env('RIOT_GAMES_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Scopes
    |--------------------------------------------------------------------------
    |
    | The default OAuth scopes to request when redirecting to Riot Games.
    | Available scopes: openid, offline_access, email
    |
    */

    'default_scopes' => [
        'openid',
        'offline_access',
        'email',
    ],
];
