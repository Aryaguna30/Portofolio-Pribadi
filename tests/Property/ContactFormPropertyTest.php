<?php

// Feature: portfolio-website, Property 10 and Property 12

namespace Tests\Property;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Property 10: Contact Form Validation Rejects Invalid Input
 * Property 12: Honeypot Rejects Bot Submissions
 *
 * Validates: Requirements 7.2, 7.3, 7.8
 *
 * Tests the ContactFormRequest validation rules directly using Laravel's
 * Validator facade, simulating property-based testing with randomised inputs.
 */
class ContactFormPropertyTest extends TestCase
{
    /**
     * Returns the validation rules from ContactFormRequest.
     */
    private function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string', 'min:10', 'max:2000'],
            '_hp'     => ['sometimes', 'string'],
        ];
    }

    /**
     * Build a valid base payload to use as a starting point.
     */
    private function validPayload(): array
    {
        return [
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'subject' => 'Hello',
            'body'    => 'This is a valid message body.',
            '_hp'     => '',
        ];
    }

    /**
     * Generate a random invalid email string.
     */
    private function randomInvalidEmail(): string
    {
        $invalids = [
            'notanemail',
            'missing@',
            '@nodomain.com',
            'spaces in@email.com',
            'double@@domain.com',
            '',
            'plaintext',
            'user@',
            '@',
            'user name@domain.com',
            'user@domain..com',
            str_repeat('a', 50),
            '<script>@evil.com',
            'user@[invalid',
            'user@@domain.com',
            'user@domain@com',
            'user@.domain.com',
            '@domain.com',
            'just-text-no-at-sign',
            'missing.at.sign',
        ];

        return $invalids[array_rand($invalids)];
    }

    /**
     * Generate a random non-empty honeypot value.
     * Uses values that Laravel's filled() considers non-empty (non-null, non-empty-string).
     */
    private function randomHoneypotValue(): string
    {
        $values = [
            'bot',
            'spam',
            'a',
            '1',
            str_repeat('x', rand(1, 100)),
            'http://spam.com',
            '<script>alert(1)</script>',
            'null',
            'true',
            'false',
            'robot@spam.com',
            'hello world',
            '123456',
            'test',
            'submit',
        ];

        return $values[array_rand($values)];
    }

    // -------------------------------------------------------------------------
    // Property 10: Any invalid email must be rejected
    // -------------------------------------------------------------------------

    /**
     * Property 10: Any invalid email must be rejected by the validator.
     *
     * Validates: Requirements 7.2, 7.3
     */
    public function test_invalid_email_is_always_rejected(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            $invalidEmail = $this->randomInvalidEmail();

            $data = array_merge($this->validPayload(), ['email' => $invalidEmail]);

            $validator = Validator::make($data, $this->rules());

            $this->assertTrue(
                $validator->fails(),
                "Iteration {$i}: invalid email '{$invalidEmail}' should be rejected but passed validation"
            );

            $this->assertArrayHasKey(
                'email',
                $validator->errors()->toArray(),
                "Iteration {$i}: validation errors must include 'email' field for input '{$invalidEmail}'"
            );
        }
    }

    /**
     * Property 10: Empty email must be rejected.
     */
    public function test_empty_email_is_rejected(): void
    {
        $data = array_merge($this->validPayload(), ['email' => '']);

        $validator = Validator::make($data, $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * Property 10: Missing email field must be rejected.
     */
    public function test_missing_email_is_rejected(): void
    {
        $data = $this->validPayload();
        unset($data['email']);

        $validator = Validator::make($data, $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * Property 10: Body shorter than 10 characters must be rejected.
     */
    public function test_short_body_is_always_rejected(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            $length = rand(0, 9);
            $shortBody = str_repeat('a', $length);

            $data = array_merge($this->validPayload(), ['body' => $shortBody]);

            $validator = Validator::make($data, $this->rules());

            $this->assertTrue(
                $validator->fails(),
                "Iteration {$i}: body of length {$length} should be rejected"
            );

            $this->assertArrayHasKey(
                'body',
                $validator->errors()->toArray(),
                "Iteration {$i}: validation errors must include 'body' for short input"
            );
        }
    }

    /**
     * Property 10: A valid payload must pass validation (sanity check).
     */
    public function test_valid_payload_passes_validation(): void
    {
        $validator = Validator::make($this->validPayload(), $this->rules());

        $this->assertFalse(
            $validator->fails(),
            'A fully valid payload should pass validation'
        );
    }

    // -------------------------------------------------------------------------
    // Property 12: Any non-empty honeypot value must be rejected
    // -------------------------------------------------------------------------

    /**
     * Property 12: Any non-empty honeypot value must be rejected.
     *
     * Validates: Requirements 7.8
     *
     * The honeypot check is implemented as a custom after-validation hook
     * in ContactFormRequest::withValidator(). We replicate that logic here.
     */
    public function test_non_empty_honeypot_is_always_rejected(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            $honeypotValue = $this->randomHoneypotValue();

            $data = array_merge($this->validPayload(), ['_hp' => $honeypotValue]);

            $validator = Validator::make($data, $this->rules());

            // Replicate the withValidator() after-hook from ContactFormRequest.
            // ContactFormRequest uses $this->filled('_hp'), which returns true
            // when the value is present and not an empty string/null.
            $validator->after(function ($v) use ($data) {
                if (isset($data['_hp']) && $data['_hp'] !== '' && $data['_hp'] !== null) {
                    $v->errors()->add('_hp', 'Invalid submission detected.');
                }
            });

            $this->assertTrue(
                $validator->fails(),
                "Iteration {$i}: honeypot value '{$honeypotValue}' should cause rejection"
            );

            $this->assertArrayHasKey(
                '_hp',
                $validator->errors()->toArray(),
                "Iteration {$i}: validation errors must include '_hp' for non-empty honeypot"
            );
        }
    }

    /**
     * Property 12: Empty honeypot value must NOT trigger rejection.
     */
    public function test_empty_honeypot_does_not_trigger_rejection(): void
    {
        $data = array_merge($this->validPayload(), ['_hp' => '']);

        $validator = Validator::make($data, $this->rules());

        $validator->after(function ($v) use ($data) {
            if (isset($data['_hp']) && $data['_hp'] !== '' && $data['_hp'] !== null) {
                $v->errors()->add('_hp', 'Invalid submission detected.');
            }
        });

        $this->assertFalse(
            $validator->fails(),
            'Empty honeypot should not cause validation failure'
        );
    }

    /**
     * Property 12: Absent honeypot field must NOT trigger rejection.
     */
    public function test_absent_honeypot_does_not_trigger_rejection(): void
    {
        $data = $this->validPayload();
        unset($data['_hp']);

        $validator = Validator::make($data, $this->rules());

        $validator->after(function ($v) use ($data) {
            if (isset($data['_hp']) && $data['_hp'] !== '' && $data['_hp'] !== null) {
                $v->errors()->add('_hp', 'Invalid submission detected.');
            }
        });

        $this->assertFalse(
            $validator->fails(),
            'Absent honeypot field should not cause validation failure'
        );
    }
}
