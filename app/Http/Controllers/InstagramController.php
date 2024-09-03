<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramController extends Controller
{
    private $accessToken;
    private $apiVersion = 'v18.0';
    private $verifyToken;

    public function __construct()
    {
        $this->accessToken = config('services.instagram.access_token');
        $this->verifyToken = config('services.instagram.webhook_verify_token');
    }

    public function sendPost(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image_url' => 'required|url',
            'caption' => 'required|string',
        ]);

        $endpoint = "https://graph.facebook.com/{$this->apiVersion}/me/media";

        $response = Http::post($endpoint, [
            'image_url' => $request->image_url,
            'caption' => $request->caption,
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            $creationId = $response->json()['id'];

            $publishEndpoint = "https://graph.facebook.com/{$this->apiVersion}/me/media_publish";
            $publishResponse = Http::post($publishEndpoint, [
                'creation_id' => $creationId,
                'access_token' => $this->accessToken,
            ]);

            if ($publishResponse->successful()) {
                return response()->json(['message' => 'Post published successfully', 'id' => $publishResponse->json()['id']]);
            }
        }

        return response()->json(['error' => 'Failed to create post'], 500);
    }

    public function readMessages(): \Illuminate\Http\JsonResponse
    {
        $endpoint = "https://graph.facebook.com/{$this->apiVersion}/me/conversations";

        $response = Http::get($endpoint, [
            'fields' => 'participants,messages{message,from,to,created_time}',
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            $conversations = $response->json()['data'];

            foreach ($conversations as $convo) {
                $this->saveConversation($convo);
            }

            return response()->json(['message' => 'Conversations and messages saved successfully']);
        }

        return response()->json(['error' => 'Failed to fetch messages'], 500);
    }

    public function receiveWebhook(Request $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        if ($request->isMethod('get')) {
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            if ($mode === 'subscribe' && $token === $this->verifyToken) {
                Log::info('Webhook verified successfully');
                return response($challenge, 200);
            }

            Log::error('Webhook verification failed');
            return response('Forbidden', 403);
        }

        $payload = $request->all();
        Log::info('Received webhook payload', $payload);

        if (isset($payload['entry'][0]['changes'][0]['field']) && $payload['entry'][0]['changes'][0]['field'] == 'messages') {
            $change = $payload['entry'][0]['changes'][0];
            $value = $change['value'];

            if (isset($value['messages'])) {
                foreach ($value['messages'] as $message) {
                    $senderId = $value['from']['id'];
                    $messageText = $message['text'] ?? null;
                    $messageId = $message['id'];

                    if ($messageText) {
                        $this->processIncomingMessage($senderId, $messageText, $messageId);
                    }
                }
            }
        }

        return response()->json(['status' => 'OK']);
    }

    private function processIncomingMessage($senderId, $messageText, $messageId): void
    {
        Log::info("Received Instagram message from {$senderId}: {$messageText}");

        $conversation = Conversation::firstOrCreate(
            ['participant_id' => $senderId],
            ['instagram_conversation_id' => 'temp_' . $senderId]
        );

        $message = new Message([
            'instagram_message_id' => $messageId,
            'sender_id' => $senderId,
            'message' => $messageText,
            'sent_at' => now(),
        ]);

        $conversation->messages()->save($message);

        $this->sendReply($senderId, "Thank you for your message: {$messageText}");
    }

    private function saveConversation($convoData): void
    {
        $participantId = $convoData['participants']['data'][0]['id'];

        $conversation = Conversation::firstOrCreate(
            ['instagram_conversation_id' => $convoData['id']],
            ['participant_id' => $participantId]
        );

        foreach ($convoData['messages']['data'] as $messageData) {
            $message = new Message([
                'instagram_message_id' => $messageData['id'],
                'sender_id' => $messageData['from']['id'],
                'message' => $messageData['message'],
                'sent_at' => $messageData['created_time'],
            ]);

            $conversation->messages()->save($message);
        }
    }

    private function sendReply($recipientId, $messageText): void
    {
        $endpoint = "https://graph.facebook.com/{$this->apiVersion}/me/messages";

        $response = Http::post($endpoint, [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $messageText],
            'access_token' => $this->accessToken,
        ]);

        if (!$response->successful()) {
            Log::error("Failed to send reply: " . $response->body());
        }
    }
}
