<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InstagramAuthController extends Controller
{
    public function redirectToInstagram()
    {
        $clientId = config('services.instagram.client_id');
        $redirectUri = config('services.instagram.redirect_uri');

        $authUrl = "https://api.instagram.com/oauth/authorize?"
            . "client_id={$clientId}"
            . "&redirect_uri=" . urlencode($redirectUri)
            . "&scope=user_profile,user_media"
            . "&response_type=code";

        Log::info('Redirecting to Instagram with URL: ' . $authUrl);

        return redirect($authUrl);
    }

    public function handleCallback(Request $request): \Illuminate\Http\RedirectResponse
    {
        $code = $request->query('code');

        if (!$code) {
            return redirect()->route('home')->with('error', 'Authorization code not received');
        }

        try {
            $accessToken = $this->getAccessToken($code);
            $userInfo = $this->getUserInfo($accessToken);

            // Save or update user
            $user = User::updateOrCreate(
                ['instagram_id' => $userInfo['id']],
                [
                    'name' => $userInfo['username'],
                    'instagram_access_token' => $accessToken,
                ]
            );

            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Instagram account connected successfully');
        } catch (\Exception $e) {
            Log::error('Instagram authentication error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Failed to connect Instagram account: ' . $e->getMessage());
        }
    }

    private function getAccessToken($code)
    {
        $clientId = config('services.instagram.client_id');
        $clientSecret = config('services.instagram.client_secret');
        $redirectUri = config('services.instagram.redirect_uri');

        Log::info('Attempting to get access token with:', [
            'client_id' => $clientId,
            'client_secret' => substr($clientSecret, 0, 5) . '...',
            'redirect_uri' => $redirectUri,
            'code' => substr($code, 0, 5) . '...',
        ]);

        $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            Log::error('Failed to obtain access token. Response: ' . $response->body());
            throw new \Exception('Failed to obtain access token: ' . $response->body());
        }

        return $response->json()['access_token'];
    }

    private function getUserInfo($accessToken)
    {
        $response = Http::get("https://graph.instagram.com/me", [
            'fields' => 'id,username',
            'access_token' => $accessToken,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to get user info: ' . $response->body());
        }

        return $response->json();
    }
}
