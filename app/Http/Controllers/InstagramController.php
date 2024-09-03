<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramController extends Controller
{
    private $accessToken;
    private $apiVersion = 'v20.0';

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

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Received webhook payload', $payload);

        if ($payload['object'] === 'instagram') {
            foreach ($payload['entry'] as $entry) {
                $instagramAccountId = $entry['id'];
                Log::info("Processing entry for Instagram Account ID: {$instagramAccountId}");

                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $messagingEvent) {
                        $senderId = $messagingEvent['sender']['id'];
                        $recipientId = $messagingEvent['recipient']['id'];

                        Log::info("Message event: Sender ID: {$senderId}, Recipient ID: {$recipientId}");

                        if (isset($messagingEvent['message'])) {
                            $message = $messagingEvent['message']['text'];
                            $messageId = $messagingEvent['message']['mid'];
                            $isEcho = $messagingEvent['message']['is_echo'] ?? false;

                            if ($isEcho) {
                                Log::info("Echo message detected. Skipping processing.");
                                continue;
                            }

                            $this->handleIncomingMessage($senderId, $recipientId, $message, $messageId);
                        }
                    }
                }
            }
        }

        return response()->json(['status' => 'OK']);
    }

    protected function handleIncomingMessage($senderId, $recipientId, $message, $messageId): void
    {
        Log::info("Received message from {$senderId} to {$recipientId}: {$message}");

        // Here you can add logic to process the message
        // For example, you might want to save it to your database

        // Only send a reply if it's not an echo message (i.e., when sender is not the page itself)
        if ($senderId !== $recipientId) {
            $this->sendReply($senderId, "Thanks for your message: {$message}");
        }
    }

    protected function processEntry($entry): void
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



    public function sendReply(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'recipient_id' => 'required|string',
            'message' => 'required|string',
        ]);

        $recipientId = $request->input('recipient_id');
        $message = $request->input('message');
        $instagramAccountId = '407346398980250'; // Your Instagram Account ID

        $url = "https://graph.facebook.com/v18.0/{$instagramAccountId}/messages";

        try {
            $response = Http::post($url, [
                'recipient_id' => $recipientId,
                'message' => json_encode(['text' => $message]),
                'access_token' => $this->accessToken,
            ]);

            $responseBody = $response->json();

            if ($response->successful()) {
                Log::info("Reply sent successfully to {$recipientId}", ['response' => $responseBody]);
                return response()->json(['status' => 'success', 'message' => 'Reply sent successfully']);
            } else {
                Log::error("Failed to send reply to {$recipientId}", [
                    'status_code' => $response->status(),
                    'response' => $responseBody,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to send reply',
                    'error_details' => $responseBody['error'] ?? 'Unknown error'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("Exception occurred while sending reply", [
                'recipient_id' => $recipientId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'An exception occurred while sending the reply',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }
}
