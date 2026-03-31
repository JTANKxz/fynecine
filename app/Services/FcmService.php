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
                    'data' => [
                        'title' => (string) $notificationData['title'],
                        'message' => (string) $notificationData['body'],
                        'image_url' => (string) ($notificationData['image_url'] ?? ''),
                        'big_picture_url' => (string) ($notificationData['big_picture_url'] ?? ''),
                        'action_type' => (string) ($notificationData['action_type'] ?? 'none'),
                        'action_value' => (string) ($notificationData['action_value'] ?? ''),
                    ],
                ],
            ];

            // For Android, we keep it data-only to trigger onMessageReceived
            // We can add android specific high priority here
            $payload['message']['android'] = [
                'priority' => 'high',
            ];

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
