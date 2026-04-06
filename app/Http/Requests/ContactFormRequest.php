<?php

namespace App\Http\Requests;

use App\Services\TurnstileService;
use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email:rfc'],
            'subject'            => ['required', 'string', 'max:255'],
            'body'               => ['required', 'string', 'min:10', 'max:2000'],
            '_hp'                => ['sometimes', 'string'],
            'cf_turnstile_token' => ['sometimes', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Honeypot check
            if ($this->filled('_hp')) {
                $validator->errors()->add('_hp', 'Invalid submission detected.');
                return;
            }

            // Turnstile verification (only when secret key is configured)
            if (! empty(config('services.turnstile.secret_key'))) {
                $token = $this->input('cf_turnstile_token');
                $service = app(TurnstileService::class);
                if (! $service->verify($token, $this->ip())) {
                    $validator->errors()->add('cf_turnstile_token', 'Verifikasi CAPTCHA gagal. Silakan coba lagi.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Strip newlines to prevent email header injection
        // Also ensure _hp and cf_turnstile_token are always strings (not null)
        $this->merge([
            'name'               => preg_replace('/[\r\n]/', '', $this->name ?? ''),
            'subject'            => preg_replace('/[\r\n]/', '', $this->subject ?? ''),
            '_hp'                => (string) ($this->input('_hp') ?? ''),
            'cf_turnstile_token' => (string) ($this->input('cf_turnstile_token') ?? ''),
        ]);

        if ($this->has('body')) {
            $this->merge([
                'body' => strip_tags($this->body),
            ]);
        }
    }
}
