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
            // Extract and validate webhook data
            $webhookData = $request->all();

            // Ensure payload is for a WhatsApp Business Account and contains changes
            if ($this->validateWebhook($webhookData)) {
                foreach ($webhookData['entry'] as $entry) {
                    foreach ($entry['changes'] as $change) {
                        if ($change['field'] === 'messages') {
                            // Check for and process message statuses
                            if (isset($change['value']['statuses'])) {
                                $statuses = $change['value']['statuses'];
                                foreach ($statuses as $status) {
                                    if ($status['status'] === 'read') {
                                        $recipientId = $status['recipient_id'];
                                        $messageId = $status['id'];
                                        $timestamp = $status['timestamp'];

                                        // Handle the "Message Read" status
                                        $this->handleMessageReadStatus($recipientId, $messageId, $timestamp);
                                    }
                                }
                            }

                            // Check for and process received messages
                            if (isset($change['value']['messages'])) {
                                $messages = $change['value']['messages'];
                                foreach ($messages as $message) {
                                    if ($message['type'] === 'text' && isset($message['text']['body'])) {
                                        $recipientNumber = $message['from'];
                                        $userMessage = $message['text']['body'];

                                        // Generate response using Gemini API
                                        $responseText = $this->getGeminiResponse($userMessage);

                                        // Send the response back via WhatsApp
                                        Whatsapp::send($recipientNumber, TextMessage::create($responseText));
                                    }
                                }
                            }
                        }
                    }
                }

                return response()->json(['message' => 'Webhook processed successfully'], 200);
            } else {
                // Log invalid webhook payload
                Log::error('Invalid webhook payload');
                return response()->json(['error' => 'Invalid webhook payload'], 400);
            }
        } catch (\Exception $e) {
            // Log any exceptions
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the webhook'], 500);
        }
    }

    protected function validateWebhook($webhookData)
    {
        // Example validation: check for valid structure and required fields
        if (!isset($webhookData['object']) || $webhookData['object'] !== 'whatsapp_business_account') {
            return false;
        }
        if (!isset($webhookData['entry']) || !is_array($webhookData['entry'])) {
            return false;
        }
        return true;
    }

    protected function getGeminiResponse($userMessage)
    {
        // Retrieve the API key from the environment
        $geminiApiKey = env('GEMINI_API_KEY');

        // Construct the URL with the API key
        $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $geminiApiKey;

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
                                "text" => $userMessage,
                            ]
                        ]
                    ]
                ]
            ];

            $response = $client->post($geminiUrl, [
                'headers' => $headers,
                'json' => $body,
            ]);

            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody(), true);

                // Check if "candidates" exist and are not empty before accessing them
                if (isset($responseData['candidates']) && !empty($responseData['candidates'])) {
                    // Extract the first candidate's content
                    $generatedContent = $responseData['candidates'][0]['content']['parts'][0]['text'];
                    return $generatedContent;
                } else {
                    Log::error('Error in Gemini response structure: Missing or empty "candidates" key');
                    // Optionally return a default value or user-friendly message
                    return 'There seems to be an issue with the Gemini service. Please try again later.';
                }
            } else {
                // Error handling for non-200 status code
                $errorResponse = json_decode($response->getBody(), true);
                $errorMessage = $errorResponse['error']['message'] ?? 'Unknown error'; // Get main error message
                $errorCode = $errorResponse['error']['code'] ?? null; // Get error code (if available)

                Log::error('Error fetching response from Gemini: ' . $response->getStatusCode() . ' - ' . $errorMessage . ' (code: ' . $errorCode . ')');

                // Optionally, you can return a user-friendly error message here
                return 'There was an error processing your request. Please try again later.';
            }
        } catch (\Exception $e) {
            Log::error('Error fetching response from Gemini: ' . $e->getMessage());
            return 'Error fetching response from Gemini';
        }
    }

    protected function handleMessageReadStatus($recipientId, $messageId, $timestamp)
    {
        // Log the message read status
        Log::info("Message ID $messageId read by $recipientId at $timestamp");

        // Additional logic for handling the "Message Read" status can be added here
        // For example, updating the database, sending notifications, etc.
    }
}
