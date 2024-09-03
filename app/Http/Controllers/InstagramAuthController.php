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
        $scope = 'business_basic,business_manage_messages,business_content_publish';

        $instagramAuthUrl = "https://www.instagram.com/oauth/authorize?client_id={$clientId}&redirect_uri={$redirectUri}&scope={$scope}&response_type=code";

        return redirect($instagramAuthUrl);
    }

    public function handleCallback(Request $request)
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
        $response = Http::post('https://api.instagram.com/oauth/access_token', [
            'client_id' => config('services.instagram.client_id'),
            'client_secret' => config('services.instagram.client_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.instagram.redirect_uri'),
            'code' => $code,
        ]);

        if (!$response->successful()) {
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
