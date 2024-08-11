<?php

namespace App\Http\Controllers;

use App\Models\UserInteraction;
use App\Models\EscalatedCase;
use App\Mail\EscalationNotification;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MissaelAnda\Whatsapp\Facade\Whatsapp;
use MissaelAnda\Whatsapp\Messages\TextMessage;
use Illuminate\Support\Str;

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

    protected function validateWebhook($webhookData): bool
    {
        return isset($webhookData['object']) &&
            $webhookData['object'] === 'whatsapp_business_account' &&
            isset($webhookData['entry']) &&
            is_array($webhookData['entry']);
    }

    protected function processMessageStatuses($statuses): void
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
                $messageType = $this->classifyMessageType($userMessage);
                $responseText = $this->getGeminiResponse($userMessage);
                Whatsapp::send($recipientNumber, TextMessage::create($responseText));
                $this->markMessageAsRead($message['id']);

                try {
                    $userInteraction = UserInteraction::on('second_database')->firstOrNew(['recipient_id' => $recipientNumber]);
                    $userInteraction->recipient_id = $recipientNumber;
                    $userInteraction->user_message = $userMessage;
                    $userInteraction->bot_response = $responseText;
                    $userInteraction->type = $messageType;

                    // Append to the conversation
                    $conversation = $userInteraction->conversation ?? [];
                    $conversation[] = [
                        'timestamp' => now()->toDateTimeString(),
                        'user_message' => $userMessage,
                        'bot_response' => $responseText
                    ];
                    $userInteraction->conversation = $conversation;

                    $userInteraction->save();

                    // Check if escalation is needed
                    $escalationLevel = $this->checkEscalationNeeded($userInteraction);
                    if ($escalationLevel > 0) {
                        $this->escalateInteraction($userInteraction, $escalationLevel);
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing user interaction: ' . $e->getMessage());
                }
            }
        }
    }

    protected function classifyMessageType($message)
    {
        $message = strtolower($message);

        $inquiryKeywords = ['how', 'what', 'when', 'where', 'why', 'can you', 'is it possible'];
        $complaintKeywords = ['problem', 'issue', 'not working', 'error', 'disappointed', 'unhappy', 'doesn\'t work'];

        foreach ($inquiryKeywords as $keyword) {
            if (Str::startsWith($message, $keyword)) {
                return 'inquiry';
            }
        }

        foreach ($complaintKeywords as $keyword) {
            if (Str::contains($message, $keyword)) {
                return 'complaint';
            }
        }

        return 'request';
    }

    protected function checkEscalationNeeded(UserInteraction $interaction)
    {
        $recentInteractions = UserInteraction::on('second_database')
            ->where('recipient_id', $interaction->recipient_id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        // Check for keywords indicating urgency
        $urgentKeywords = ['urgent', 'emergency', 'immediately', 'asap'];
        $containsUrgentKeyword = Str::contains(strtolower($interaction->user_message), $urgentKeywords);

        // Escalation levels:
        // 0 - No escalation needed
        // 1 - Low priority escalation
        // 2 - Medium priority escalation
        // 3 - High priority escalation

        if ($interaction->type === 'complaint' && $containsUrgentKeyword) {
            return 3; // High priority
        } elseif ($interaction->type === 'complaint' || $recentInteractions >= 5) {
            return 2; // Medium priority
        } elseif ($recentInteractions >= 3 || $containsUrgentKeyword) {
            return 1; // Low priority
        }

        return 0; // No escalation needed
    }

    protected function escalateInteraction(UserInteraction $interaction, int $level)
    {
        $escalatedCase = new EscalatedCase();
        $escalatedCase->setConnection('second_database');
        $escalatedCase->user_interaction_id = $interaction->id;
        $escalatedCase->recipient_id = $interaction->recipient_id;
        $escalatedCase->escalation_level = $level;
        $escalatedCase->status = 'open';
        $escalatedCase->save();

        $this->notifyEscalationTeam($escalatedCase);
        $this->sendEscalationMessageToUser($interaction->recipient_id, $level);

        Log::alert('Interaction escalated', [
            'interaction_id' => $interaction->id,
            'recipient_id' => $interaction->recipient_id,
            'type' => $interaction->type,
            'escalation_level' => $level,
        ]);
    }

    protected function notifyEscalationTeam(EscalatedCase $case)
    {
        Log::info('Notifying escalation team', [
            'case_id' => $case->id,
            'recipient_id' => $case->recipient_id,
            'level' => $case->escalation_level,
        ]);

        // Get support email addresses from configuration
        $supportEmails = config('support.escalation_emails', ['default@example.com']);

        // Send email to support team
        Mail::to($supportEmails)->send(new EscalationNotification($case));
    }

    protected function sendEscalationMessageToUser($recipientId, $level): void
    {
        $messages = [
            1 => "We've noted your concern and a support representative will get back to you soon.",
            2 => "We understand your issue is important. Our priority support team has been notified and will contact you shortly.",
            3 => "We apologize for the inconvenience. This has been escalated to our highest priority team, and you will be contacted immediately.",
        ];

        $message = $messages[$level] ?? $messages[1];
        Whatsapp::send($recipientId, TextMessage::create($message));
    }

    protected function handleMessageReadStatus($recipientId, $messageId, $timestamp): void
    {
        Log::info("Message ID $messageId read by $recipientId at $timestamp");
        // Additional logic for handling the "Message Read" status can be added here
    }

    protected function markMessageAsRead($messageId): void
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
}
