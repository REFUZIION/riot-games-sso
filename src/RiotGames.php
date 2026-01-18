<?php

namespace Fuziion\RiotGamesSSO;

use Fuziion\RiotGamesSSO\Exceptions\RiotGamesException;

class RiotGames
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseAuthUrl = 'https://auth.riotgames.com';
    protected string $baseApiUrl = 'https://europe.api.riotgames.com';

    public function __construct(?string $clientId = null, ?string $clientSecret = null)
    {
        $this->clientId = $clientId ?? $this->getConfig('client_id', '');
        $this->clientSecret = $clientSecret ?? $this->getConfig('client_secret', '');
        
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new RiotGamesException(
                'Riot Games Client ID and Client Secret are required. ' .
                'Provide them via constructor parameters or Laravel config (riot-games-sso.client_id and riot-games-sso.client_secret).'
            );
        }
    }

    /**
     * Get configuration value (works with Laravel config or direct values)
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        if (function_exists('config')) {
            return config("riot-games-sso.{$key}", $default);
        }

        return $default;
    }

    /**
     * Get Riot account data using access token
     *
     * @param string $accessToken The OAuth access token
     * @return array Account data from Riot Games API
     * @throws RiotGamesException
     */
    public function getRiotAccountData(string $accessToken): array
    {
        $url = "{$this->baseApiUrl}/riot/account/v1/accounts/me";

        $response = $this->makeRequest('GET', $url, [
            'Authorization' => "Bearer {$accessToken}",
            'Accept' => 'application/json',
        ]);

        return $response;
    }

    /**
     * Exchange authorization code for access token
     *
     * @param string $code The authorization code from the callback
     * @param string $redirectUri The redirect URI used in the authorization request
     * @return string|null The access token or null if not found
     * @throws RiotGamesException
     */
    public function getRiotAuthToken(string $code, string $redirectUri): ?string
    {
        $url = "{$this->baseAuthUrl}/token";

        $response = $this->makeRequest('POST', $url, [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ], [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $this->clientId,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion' => $this->clientSecret,
        ]);

        return $response['access_token'] ?? null;
    }

    /**
     * Generate the authorization URL for redirecting users to Riot Games login
     *
     * @param string $redirectUri The callback URL where Riot will redirect after authentication
     * @param array $scopes Additional scopes (default: openid, offline_access, email)
     * @return string The full authorization URL
     */
    public function getAuthorizationUrl(string $redirectUri, array $scopes = ['openid', 'offline_access', 'email']): string
    {
        $scopeString = implode('+', $scopes);
        $params = http_build_query([
            'redirect_uri' => $redirectUri,
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'scope' => $scopeString,
        ]);

        return "{$this->baseAuthUrl}/authorize?{$params}";
    }

    /**
     * Make HTTP request using native PHP cURL
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $url The full URL
     * @param array $headers HTTP headers
     * @param array|null $data Request body data (for POST requests)
     * @return array Decoded JSON response
     * @throws RiotGamesException
     */
    protected function makeRequest(string $method, string $url, array $headers = [], ?array $data = null): array
    {
        if (!function_exists('curl_init')) {
            throw new RiotGamesException('cURL extension is required but not available.');
        }

        $ch = curl_init();

        $curlHeaders = [];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = "{$key}: {$value}";
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                if (isset($headers['Content-Type']) && str_contains($headers['Content-Type'], 'application/x-www-form-urlencoded')) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    $curlHeaders[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
                }
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new RiotGamesException("cURL error: {$error}");
        }

        if ($httpCode >= 400) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error_description'] ?? $errorData['error'] ?? "HTTP {$httpCode} error";
            throw new RiotGamesException($errorMessage, $httpCode);
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RiotGamesException('Invalid JSON response from Riot Games API: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
