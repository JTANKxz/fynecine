<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(config('services.firebase.service_account'));
        $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    }

    /**
     * Get OAuth2 Access Token
     */
    public function getAccessToken()
    {
        $this->client->fetchAccessTokenWithAssertion();
        $accessToken = $this->client->getAccessToken();
        return $accessToken['access_token'];
    }

    /**
     * Send Push Notification to multiple tokens
     */
    public function sendPush(array $tokens, array $notificationData)
    {
        if (empty($tokens)) {
            return [];
        }

        try {
            $accessToken = $this->getAccessToken();
        } catch (\Exception $e) {
            Log::error('FCM Auth Error: ' . $e->getMessage());
            return [];
        }

        $projectId = config('services.firebase.project_id', 'fynelabs');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $results = [];

        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $notificationData['title'],
                        'body' => $notificationData['body'],
                    ],
                    'data' => [
                        'title' => $notificationData['title'],
                        'message' => $notificationData['body'],
                        'image_url' => $notificationData['image_url'] ?? '',
                        'big_picture_url' => $notificationData['big_picture_url'] ?? '',
                        'action_type' => $notificationData['action_type'] ?? 'none',
                        'action_value' => $notificationData['action_value'] ?? '',
                    ],
                ],
            ];

            // Android specific customizations (Big Picture support via notification field if needed)
            if (!empty($notificationData['big_picture_url'])) {
                $payload['message']['android'] = [
                    'notification' => [
                        'image' => $notificationData['big_picture_url'],
                    ],
                ];
            }

            try {
                $response = Http::withToken($accessToken)
                    ->timeout(10)
                    ->post($url, $payload);

                $results[] = [
                    'token' => $token,
                    'status' => $response->successful(),
                    'response' => $response->json(),
                ];

                if (!$response->successful()) {
                    Log::error('FCM Send Individual Error', [
                        'token' => $token,
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('FCM Request Exception: ' . $e->getMessage(), ['token' => $token]);
                $results[] = [
                    'token' => $token,
                    'status' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
