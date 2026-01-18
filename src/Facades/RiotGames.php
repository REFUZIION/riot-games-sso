<?php

namespace Fuziion\RiotGamesSSO\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getRiotAccountData(string $accessToken)
 * @method static string|null getRiotAuthToken(string $code, string $redirectUri)
 * @method static string getAuthorizationUrl(string $redirectUri, array $scopes = ['openid', 'offline_access', 'email'])
 *
 * @see \Fuziion\RiotGamesSSO\RiotGames
 */
class RiotGames extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'riot-games-sso';
    }
}
