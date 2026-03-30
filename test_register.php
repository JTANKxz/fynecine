<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://localhost:8000/api/',
    'http_errors' => false,
]);

$random = rand(1000, 9999);
$name = "Test User " . $random;
$username = "testuser" . $random;
$email = "test" . $random . "@example.com";
$password = "password123";

echo "Testing registration for: $email\n";

$response = $client->post('auth/register', [
    'json' => [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
    ],
]);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Body: " . $response->getBody() . "\n";
