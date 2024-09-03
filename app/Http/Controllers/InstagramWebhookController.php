<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class InstagramWebhookController extends Controller
{
    private $verifyToken;
    private $instagramApiController;

    public function __construct(InstagramController $instagramApiController)
    {
        $this->verifyToken = config('services.instagram.webhook_verify_token');
        $this->instagramApiController = $instagramApiController;
    }

    public function handleWebhook(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->verifyWebhook($request);
        }

        return $this->processWebhook($request);
    }

    private function verifyWebhook(Request $request)
    {
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

    private function processWebhook(Request $request)
    {
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

        // Here you can add your custom logic to handle incoming messages
        // For example, you might want to save the message to your database
        // or trigger some automated response

        // For this example, we'll just send a simple reply
        $this->instagramApiController->sendMessage(new Request([
            'recipient_id' => $senderId,
            'message' => "Thanks for your message: {$messageText}"
        ]));
    }
}
