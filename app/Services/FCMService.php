<?php

namespace App\Services;

use Google\Client;
use GuzzleHttp\Client as HttpClient;

class FCMService
{
    protected static function getAccessToken()
    {
        $path = storage_path('app/fcm-key.json');

        // 🔐 أنشئ الملف من Base64 لو غير موجود
        if (!file_exists($path)) {
            $base64 = env('FCM_CREDENTIALS_BASE64');

            if (!$base64) {
                throw new \Exception('FCM credentials not found');
            }

            file_put_contents($path, base64_decode($base64));
        }

        $client = new Client();
        $client->setAuthConfig($path);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $tokenData = $client->fetchAccessTokenWithAssertion();

        if (!isset($tokenData['access_token'])) {
            throw new \Exception('Failed to get Firebase access token');
        }

        return $tokenData['access_token'];
    }

    public static function send($deviceToken, $title, $body, $data = [])
    {
        try {
            $projectId = env('FIREBASE_PROJECT_ID');
            $accessToken = self::getAccessToken();

            // 🧠 كل البيانات Strings
            $stringData = [];
            foreach ($data as $k => $v) {
                $stringData[$k] = (string) $v;
            }

            $message = [
                "message" => [
                    "token" => $deviceToken,

                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                    ],

                    "android" => [
                        "priority" => "HIGH",
                        "notification" => [
                            "sound" => "default",
                            "channel_id" => "high_importance_channel",
                            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                        ],
                    ],

                    "data" => array_merge($stringData, [
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    ]),
                ]
            ];

            $http = new HttpClient();
            $response = $http->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                [
                    "headers" => [
                        "Authorization" => "Bearer {$accessToken}",
                        "Content-Type" => "application/json",
                    ],
                    "json" => $message,
                ]
            );

            return json_decode($response->getBody(), true);

        } catch (\Throwable $e) {
            // ❌ لا تكسر النظام
            logger('FCM ERROR: ' . $e->getMessage());
            return null;
        }
    }
}
