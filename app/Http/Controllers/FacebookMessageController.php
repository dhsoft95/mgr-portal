<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FacebookMessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        // Extract recipient IDs from the request
        $recipients = $request->input('recipients');

        // Ensure recipients are provided
        if (!is_array($recipients) || empty($recipients)) {
            return response()->json(['error' => 'Recipients not provided'], 400);
        }

        // Define the message data
        $messageData = [
            'messaging_product' => 'whatsapp',
            'type' => 'template',
            'template' => [
                'name' => 'soft_launch_',
                'language' => [
                    'code' => 'en_US'
                ]
            ]
        ];

        // Iterate through recipients and send messages
        $responses = [];
        foreach ($recipients as $recipient) {
            $messageData['to'] = $recipient;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer EAAGlJOhhKSQBO8ZAGGORPlI8hF62FsEwZARCzLvHbonBSlsR0fM42glonrQBqo7yV1OsCYIKCypiR0pBZC5SEHFJbmY9jrH9ZAcS7NCmPfiNjxdC8wKB1HA63IxbYKgQ3ZAa7ZBb8eJZAOxkZBv9n9VWVeZBYKceXQM09m7PZC2Q5RcjgyZCHqyPcYIP6zybJafy9uWAwvjtBIghxbOnnFp994ZD',
                'Content-Type' => 'application/json',
            ])->post('https://graph.facebook.com/v19.0/333417463184055/messages', $messageData);

            $responses[] = $response->json();
        }

        return response()->json($responses);
    }


}
