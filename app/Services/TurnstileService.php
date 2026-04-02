<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Verify a Cloudflare Turnstile token.
     * Returns true if valid, false otherwise.
     * If no secret key is configured, skips verification (dev mode).
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        $secret = config('services.turnstile.secret_key');

        // Skip verification if not configured (local/dev environment)
        if (empty($secret)) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $payload = ['secret' => $secret, 'response' => $token];
            if ($ip) {
                $payload['remoteip'] = $ip;
            }

            $response = Http::timeout(5)->asForm()->post(self::VERIFY_URL, $payload);

            if (! $response->successful()) {
                Log::warning('TurnstileService: HTTP error', ['status' => $response->status()]);
                return false;
            }

            return (bool) ($response->json('success') ?? false);
        } catch (\Throwable $e) {
            Log::warning('TurnstileService: Verification failed', ['message' => $e->getMessage()]);
            // Fail open on network errors to avoid blocking legitimate users
            return true;
        }
    }
}
