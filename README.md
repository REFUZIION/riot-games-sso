# Riot Games SSO for PHP

[![Latest Version](https://img.shields.io/packagist/v/fuziion/riot-games-sso.svg?style=flat-square)](https://packagist.org/packages/fuziion/riot-games-sso)
[![Total Downloads](https://img.shields.io/packagist/dt/fuziion/riot-games-sso.svg?style=flat-square)](https://packagist.org/packages/fuziion/riot-games-sso)
[![License](https://img.shields.io/packagist/l/fuziion/riot-games-sso.svg?style=flat-square)](https://packagist.org/packages/fuziion/riot-games-sso)

A simple and efficient PHP package for implementing Riot Games SSO (Single Sign-On) authentication. Works with **any PHP project** including Laravel, Symfony, CodeIgniter, or plain PHP. This package uses native PHP cURL, requiring no external HTTP client dependencies.

## Features

- ✅ **Zero Dependencies** - Uses native PHP cURL, no Guzzle or other HTTP clients required
- ✅ **Laravel Integration** - Seamless integration with Laravel via Service Provider and Facade
- ✅ **Framework Agnostic** - Can be used in any PHP project
- ✅ **Simple API** - Clean and intuitive methods for OAuth flow
- ✅ **Error Handling** - Comprehensive exception handling
- ✅ **Type Safe** - Full type hints and return types

## Installation

Install the package via Composer:

```bash
composer require fuziion/riot-games-sso
```

## Quick Start

### Plain PHP / Framework Agnostic Usage

The package works out of the box in any PHP project. Simply instantiate the class with your credentials:

> 💡 **Tip:** See `EXAMPLE_STANDALONE.php` for a complete working example in a standalone PHP project.

```php
require 'vendor/autoload.php';

use Fuziion\RiotGamesSSO\RiotGames;
use Fuziion\RiotGamesSSO\Exceptions\RiotGamesException;

// Initialize with your credentials
$riotGames = new RiotGames(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret'
);

try {
    // Step 1: Get authorization URL and redirect user
    $authUrl = $riotGames->getAuthorizationUrl('https://yourapp.com/callback');
    header("Location: {$authUrl}");
    exit;
    
    // Step 2: Handle callback (after user authorizes)
    $code = $_GET['code'] ?? null;
    if ($code) {
        $accessToken = $riotGames->getRiotAuthToken($code, 'https://yourapp.com/callback');
        
        if ($accessToken) {
            $accountData = $riotGames->getRiotAccountData($accessToken);
            $accountData['puuid'] - Player UUID
            $accountData['gameName'] - In-game name
            $accountData['tagLine'] - Tag line
        }
    }
} catch (RiotGamesException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Laravel Setup

1. Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag=riot-games-sso-config
```

2. Add your Riot Games credentials to your `.env` file:

```env
RIOT_GAMES_CLIENT_ID=your_client_id
RIOT_GAMES_CLIENT_SECRET=your_client_secret
```

The service provider and facade are auto-discovered, so no manual registration is needed.

## Usage

### Laravel Usage

#### Using the Facade

```php
use Fuziion\RiotGamesSSO\Facades\RiotGames;

// Redirect user to Riot Games login
public function redirectToRiot()
{
    $callbackUrl = route('riot.callback');
    return redirect(RiotGames::getAuthorizationUrl($callbackUrl));
}

// Handle the callback
public function handleCallback(Request $request)
{
    try {
        $code = $request->get('code');
        $callbackUrl = route('riot.callback');
        
        // Exchange code for access token
        $accessToken = RiotGames::getRiotAuthToken($code, $callbackUrl);
        
        if ($accessToken) {
            // Get user account data
            $accountData = RiotGames::getRiotAccountData($accessToken);
            
            // $accountData contains:
            // - puuid: Player UUID
            // - gameName: In-game name
            // - tagLine: Tag line
            
            // Handle user authentication/login here
            // ...
        }
    } catch (\Fuziion\RiotGamesSSO\Exceptions\RiotGamesException $e) {
        // Handle error
        return redirect()->back()->with('error', $e->getMessage());
    }
}
```

#### Using Dependency Injection

```php
use Fuziion\RiotGamesSSO\RiotGames;

class RiotAuthController extends Controller
{
    public function __construct(
        protected RiotGames $riotGames
    ) {}
    
    public function redirectToRiot()
    {
        $callbackUrl = route('riot.callback');
        return redirect($this->riotGames->getAuthorizationUrl($callbackUrl));
    }
    
    public function handleCallback(Request $request)
    {
        $code = $request->get('code');
        $callbackUrl = route('riot.callback');
        
        $accessToken = $this->riotGames->getRiotAuthToken($code, $callbackUrl);
        $accountData = $this->riotGames->getRiotAccountData($accessToken);
        
        // Handle authentication...
    }
}
```

### Other PHP Frameworks (Symfony, CodeIgniter, etc.)

The package works the same way in any PHP framework. Just instantiate the class with your credentials:

```php
use Fuziion\RiotGamesSSO\RiotGames;
use Fuziion\RiotGamesSSO\Exceptions\RiotGamesException;

// In your controller or service
$riotGames = new RiotGames(
    clientId: getenv('RIOT_GAMES_CLIENT_ID'),
    clientSecret: getenv('RIOT_GAMES_CLIENT_SECRET')
);

// Get authorization URL
$authUrl = $riotGames->getAuthorizationUrl('https://yourapp.com/callback');

// Exchange code for token
$accessToken = $riotGames->getRiotAuthToken($code, $redirectUri);

// Get account data
$accountData = $riotGames->getRiotAccountData($accessToken);
```

## API Reference

### Methods

#### `getAuthorizationUrl(string $redirectUri, array $scopes = ['openid', 'offline_access', 'email']): string`

Generate the authorization URL for redirecting users to Riot Games login.

**Parameters:**
- `$redirectUri` - The callback URL where Riot will redirect after authentication
- `$scopes` - Optional array of OAuth scopes (default: openid, offline_access, email)

**Returns:** The full authorization URL

#### `getRiotAuthToken(string $code, string $redirectUri): string|null`

Exchange the authorization code for an access token.

**Parameters:**
- `$code` - The authorization code from the callback
- `$redirectUri` - The redirect URI used in the authorization request (must match exactly)

**Returns:** The access token or null if not found

**Throws:** `RiotGamesException` on error

#### `getRiotAccountData(string $accessToken): array`

Get user account data using the access token.

**Parameters:**
- `$accessToken` - The OAuth access token

**Returns:** Array containing:
- `puuid` - Player UUID
- `gameName` - In-game name
- `tagLine` - Tag line

**Throws:** `RiotGamesException` on error

## Example Routes (Laravel)

```php
// routes/web.php
Route::get('/auth/riot', [RiotAuthController::class, 'redirectToRiot'])->name('riot.login');
Route::get('/auth/riot/callback', [RiotAuthController::class, 'handleCallback'])->name('riot.callback');
```

## Error Handling

The package throws `RiotGamesException` for all errors. Always wrap calls in try-catch blocks:

```php
use Fuziion\RiotGamesSSO\Exceptions\RiotGamesException;

try {
    $accessToken = RiotGames::getRiotAuthToken($code, $redirectUri);
} catch (RiotGamesException $e) {
    // Handle error
    // $e->getMessage() contains the error message
    // $e->getCode() contains the HTTP status code (if applicable)
}
```

## Requirements

- PHP 8.1 or higher
- cURL extension enabled
- Riot Games Developer Account with OAuth credentials

## Getting Riot Games Credentials

1. Visit the [Riot Games Developer Portal](https://developer.riotgames.com/)
2. Create a new application
3. Configure your redirect URI
4. Copy your Client ID and Client Secret

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Author

**Diederik Veenstra** (fuziion_dev)

- Website: [https://www.fuziion.nl](https://www.fuziion.nl)
- GitHub: [@REFUZIION](https://github.com/REFUZIION)
- X: [@fuziion_dev](https://x.com/fuziion_dev)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

If you encounter any issues or have questions, please open an issue on [GitHub](https://github.com/REFUZIION/riot-games-sso/issues).
