<?php

class TokenManager {
    private $tokenFile = 'token_store.json';

    public function saveToken(array $tokenData): void {
        file_put_contents($this->tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT));
    }

    public function getToken(): ?string {
        if (!file_exists($this->tokenFile)) return null;

        $data = json_decode(file_get_contents($this->tokenFile), true);
        return $data['access_token'] ?? null;
    }

    public function getFullTokenData(): ?array {
        if (!file_exists($this->tokenFile)) return null;

        return json_decode(file_get_contents($this->tokenFile), true);
    }

    public function isTokenExpired(): bool {
        $data = $this->getFullTokenData();
        if (!$data || !isset($data['expires_at'])) return true;

        return time() >= $data['expires_at'];
    }
}
