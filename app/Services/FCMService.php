<?php

namespace App\Services;

use Google\Client;
use GuzzleHttp\Client as HttpClient;

class FCMService
{
    public static function send($deviceToken, $title, $body, $data = [])
    {
        $projectId = env('FIREBASE_PROJECT_ID');

        // 1) Google Access Token
        $client = new Client();
        $client->setAuthConfig(storage_path('app/fcm-key.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $token = $client->fetchAccessTokenWithAssertion()["access_token"];

        // 2) Data fields (must be strings)
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[$k] = (string)$v;
        }

        // 3) Full message format EXACTLY like Firebase Console
        $message = [
            "message" => [
                "token" => $deviceToken,

                // THIS IS WHAT SHOWS THE NOTIFICATION
                "notification" => [
                    "title" => $title,
                    "body"  => $body,
                ],

                "android" => [
                    "priority" => "HIGH",

                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                        "sound" => "default",
                        "channel_id" => "high_importance_channel",
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    ],
                ],

                // Data (optional)
                "data" => array_merge($stringData, [
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                ]),
            ]
        ];

        // 4) Send
        $http = new HttpClient();
        $response = $http->post(
            "https://fcm.googleapis.com/v1/projects/$projectId/messages:send",
            [
                "headers" => [
                    "Authorization" => "Bearer $token",
                    "Content-Type" => "application/json",
                ],
                "json" => $message
            ]
        );

        return json_decode($response->getBody(), true);
    }
}
