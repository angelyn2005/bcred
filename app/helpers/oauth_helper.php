<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

if (!function_exists('verify_google_token')) {
    /**
     * Verify Google OAuth token and get user info
     *
     * @param string $token
     * @return array|false
     */
    function verify_google_token($token)
    {
        if (empty($token)) {
            return false;
        }

        $url = 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . urlencode($token);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || empty($response)) {
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (empty($data) || !isset($data['sub']) || !isset($data['email'])) {
            return false;
        }
        
        return [
            'provider' => 'google',
            'provider_id' => $data['sub'],
            'email' => $data['email'],
            'fullname' => $data['name'] ?? '',
            'photo' => $data['picture'] ?? null,
        ];
    }
}


