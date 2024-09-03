<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramController extends Controller
{
    private $accessToken;
    private $apiVersion = 'v18.0';

    public function __construct()
    {
        $this->accessToken = config('services.instagram.access_token');
    }

    public function publishPost(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image_url' => 'required|url',
            'caption' => 'required|string',
        ]);

        $createMediaEndpoint = "https://graph.facebook.com/{$this->apiVersion}/me/media";

        $createResponse = Http::post($createMediaEndpoint, [
            'image_url' => $request->image_url,
            'caption' => $request->caption,
            'access_token' => $this->accessToken,
        ]);

        if (!$createResponse->successful()) {
            Log::error('Failed to create media: ' . $createResponse->body());
            return response()->json(['error' => 'Failed to create media'], 500);
        }

        $creationId = $createResponse->json()['id'];

        $publishEndpoint = "https://graph.facebook.com/{$this->apiVersion}/me/media_publish";
        $publishResponse = Http::post($publishEndpoint, [
            'creation_id' => $creationId,
            'access_token' => $this->accessToken,
        ]);

        if ($publishResponse->successful()) {
            return response()->json(['message' => 'Post published successfully', 'id' => $publishResponse->json()['id']]);
        } else {
            Log::error('Failed to publish post: ' . $publishResponse->body());
            return response()->json(['error' => 'Failed to publish post'], 500);
        }
    }

    public function readMessages()
    {
        $endpoint = "https://graph.facebook.com/{$this->apiVersion}/me/conversations";

        $response = Http::get($endpoint, [
            'fields' => 'participants,messages{message,from,to,created_time}',
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            Log::error('Failed to fetch messages: ' . $response->body());
            return response()->json(['error' => 'Failed to fetch messages'], 500);
        }
    }

    public function handleWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->all();
        Log::info('Received webhook payload', $payload);

        if ($payload['object'] === 'instagram') {
            foreach ($payload['entry'] as $entry) {
                $this->processEntry($entry);
            }
        }

        return response()->json(['status' => 'OK']);
    }

    protected function processEntry($entry)
    {
        if (isset($entry['messaging'])) {
            foreach ($entry['messaging'] as $messagingEvent) {
                if (isset($messagingEvent['message'])) {
                    $senderId = $messagingEvent['sender']['id'];
                    $message = $messagingEvent['message']['text'];
                    $messageId = $messagingEvent['message']['mid'];

                    $this->handleIncomingMessage($senderId, $message, $messageId);
                }
            }
        }
    }

    protected function handleIncomingMessage($senderId, $message, $messageId)
    {
        Log::info("Received message from {$senderId}: {$message}");

        // Here you can add logic to process the message
        // For example, you might want to save it to your database

        // For now, let's just send a simple reply
        $this->sendReply($senderId, "Thanks for your message: {$message}");
    }

    public function sendReply($recipientId, $message)
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/me/messages";

        $response = Http::post($url, [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $message],
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            Log::info("Reply sent successfully to {$recipientId}");
        } else {
            Log::error("Failed to send reply to {$recipientId}: " . $response->body());
        }
    }
}
