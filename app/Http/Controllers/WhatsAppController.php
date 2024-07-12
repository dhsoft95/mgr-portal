<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MissaelAnda\Whatsapp\Facade\Whatsapp;
use MissaelAnda\Whatsapp\Messages\TextMessage;

class WhatsAppController extends Controller
{
    public function handleWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $webhookData = $request->all();

            if ($this->validateWebhook($webhookData)) {
                foreach ($webhookData['entry'] as $entry) {
                    foreach ($entry['changes'] as $change) {
                        if ($change['field'] === 'messages') {
                            if (isset($change['value']['statuses'])) {
                                $this->processMessageStatuses($change['value']['statuses']);
                            }
                            if (isset($change['value']['messages'])) {
                                $this->processReceivedMessages($change['value']['messages']);
                            }
                        }
                    }
                }

                return response()->json(['message' => 'Webhook processed successfully'], 200);
            } else {
                Log::error('Invalid webhook payload');
                return response()->json(['error' => 'Invalid webhook payload'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the webhook'], 500);
        }
    }

    protected function validateWebhook($webhookData)
    {
        return isset($webhookData['object']) &&
            $webhookData['object'] === 'whatsapp_business_account' &&
            isset($webhookData['entry']) &&
            is_array($webhookData['entry']);
    }

    protected function processMessageStatuses($statuses)
    {
        foreach ($statuses as $status) {
            if ($status['status'] === 'read') {
                $this->handleMessageReadStatus($status['recipient_id'], $status['id'], $status['timestamp']);
            }
        }
    }

    protected function processReceivedMessages($messages)
    {
        foreach ($messages as $message) {
            if ($message['type'] === 'text' && isset($message['text']['body'])) {
                $recipientNumber = $message['from'];
                $userMessage = $message['text']['body'];
                $responseText = $this->getGeminiResponse($userMessage);
                Whatsapp::send($recipientNumber, TextMessage::create($responseText));
                $this->markMessageAsRead($message['id']);
            }
        }
    }

    protected function getGeminiResponse($userMessage)
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=' . $geminiApiKey;

        $customPrompt = file_get_contents(storage_path('app/gemini_prompt.txt'));

        $customPrompt .= "\n\nUser: " . $userMessage;

        try {
            $client = new Client();
            $headers = [
                'Content-Type' => 'application/json',
            ];

            $body = [
                "contents" => [
                    [
                        "parts" => [
                            [
                                "text" => $customPrompt,
                            ]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.9,
                    "topK" => 1,
                    "topP" => 1,
                    "maxOutputTokens" => 2048,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ]
                ]
            ];

            $response = $client->post($geminiUrl, [
                'headers' => $headers,
                'json' => $body,
            ]);

            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody(), true);

                if (isset($responseData['candidates']) && !empty($responseData['candidates'])) {
                    $generatedContent = $responseData['candidates'][0]['content']['parts'][0]['text'];
                    return $generatedContent;
                } else {
                    Log::error('Error in Gemini response structure: Missing or empty "candidates" key');
                    return 'There seems to be an issue with our service. Please try again later or contact our customer support team for assistance.';
                }
            } else {
                $errorResponse = json_decode($response->getBody(), true);
                $errorMessage = $errorResponse['error']['message'] ?? 'Unknown error';
                $errorCode = $errorResponse['error']['code'] ?? null;

                Log::error('Error fetching response from Gemini: ' . $response->getStatusCode() . ' - ' . $errorMessage . ' (code: ' . $errorCode . ')');

                return 'I apologize, but I\'m having trouble processing your request right now. Please try again later or contact our customer support team for assistance.';
            }
        } catch (\Exception $e) {
            Log::error('Error fetching response from Gemini: ' . $e->getMessage());
            return 'I\'m sorry, but I\'m experiencing technical difficulties. Please try again later or contact our customer support team for immediate assistance.';
        }
    }

    protected function handleMessageReadStatus($recipientId, $messageId, $timestamp)
    {
        Log::info("Message ID $messageId read by $recipientId at $timestamp");
        // Additional logic for handling the "Message Read" status can be added here
        // For example, updating the database, sending notifications, etc.
    }

    protected function markMessageAsRead($messageId)
    {
        $phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
        $accessToken = env('WHATSAPP_ACCESS_TOKEN');

        $url = 'https://graph.facebook.com/v20.0/' . $phoneNumberId . '/messages';
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];

        $body = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ];

        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $body,
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['success']) && $responseData['success']) {
                Log::info('Message marked as read successfully: ' . $messageId);
            } else {
                Log::error('Failed to mark message as read: ' . $messageId);
            }
        } catch (\Exception $e) {
            Log::error('Error marking message as read: ' . $e->getMessage());
        }
    }
}
