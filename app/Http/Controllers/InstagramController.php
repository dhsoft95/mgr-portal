<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramController extends Controller
{
    private $apiVersion = 'v18.0';
    private $accessToken;

    public function __construct()
    {
        $this->accessToken = config('services.instagram.access_token');
    }

    public function publishPost(Request $request)
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

    public function readMessages(): \Illuminate\Http\JsonResponse
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

    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|string',
            'message' => 'required|string',
        ]);

        $endpoint = "https://graph.facebook.com/{$this->apiVersion}/me/messages";

        $response = Http::post($endpoint, [
            'recipient' => ['id' => $request->recipient_id],
            'message' => ['text' => $request->message],
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'Message sent successfully']);
        } else {
            Log::error('Failed to send message: ' . $response->body());
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }
}
