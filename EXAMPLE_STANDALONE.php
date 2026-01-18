<?php

/**
 * Example: Using Riot Games SSO in a standalone PHP project (non-Laravel)
 * 
 * This example demonstrates how to use the package in any PHP project
 * without Laravel or any other framework.
 */

require __DIR__ . '/vendor/autoload.php';

use Fuziion\RiotGamesSSO\RiotGames;
use Fuziion\RiotGamesSSO\Exceptions\RiotGamesException;

// Initialize with your Riot Games credentials
// In production, load these from environment variables or config file
$riotGames = new RiotGames(
    clientId: 'your_client_id_here',
    clientSecret: 'your_client_secret_here'
);

// Example 1: Redirect to Riot Games login
if (!isset($_GET['code'])) {
    $callbackUrl = 'https://yourapp.com/callback.php'; // Your callback URL
    $authUrl = $riotGames->getAuthorizationUrl($callbackUrl);
    
    // Redirect user to Riot Games
    header("Location: {$authUrl}");
    exit;
}

// Example 2: Handle the callback
try {
    $code = $_GET['code'] ?? null;
    
    if (!$code) {
        throw new RiotGamesException('No authorization code received');
    }
    
    $callbackUrl = 'https://yourapp.com/callback.php';
    
    // Exchange authorization code for access token
    $accessToken = $riotGames->getRiotAuthToken($code, $callbackUrl);
    
    if (!$accessToken) {
        throw new RiotGamesException('Failed to obtain access token');
    }
    
    // Get user account data
    $accountData = $riotGames->getRiotAccountData($accessToken);
    
    // Use the account data
    echo "Player UUID: " . $accountData['puuid'] . "\n";
    echo "Game Name: " . $accountData['gameName'] . "\n";
    echo "Tag Line: " . $accountData['tagLine'] . "\n";
    
    // Now you can authenticate the user in your application
    // Store the puuid, gameName, and tagLine in your database
    // Create a session or JWT token for the user
    
} catch (RiotGamesException $e) {
    // Handle errors
    echo "Error: " . $e->getMessage() . "\n";
    echo "HTTP Code: " . $e->getCode() . "\n";
}
