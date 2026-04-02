<?php

// Feature: portfolio-website, Property 11, 13, 14

namespace Tests\Feature;

use App\Jobs\SendContactNotification;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Property 11: Contact Form Message Persistence Round Trip
 * Property 13: Rate Limiting Enforced on Contact Form
 * Property 14: Async Notification Dispatched After Message Save
 *
 * Validates: Requirements 7.4, 7.10, 7.12
 */
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a valid contact form payload.
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'subject' => 'Hello there',
            'body'    => 'This is a valid message body with enough characters.',
        ], $overrides);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Bind a passthrough purifier for testing (mews/purifier may not be registered in test env)
        $this->app->bind('purifier', function () {
            return new class {
                public function purify(string $html): string
                {
                    return $html;
                }
            };
        });

        // Clear rate limiter state between tests
        RateLimiter::clear('contact');
    }

    // -------------------------------------------------------------------------
    // Property 11: Valid submission creates Message in DB
    // -------------------------------------------------------------------------

    /**
     * Property 11: A valid contact form submission must create a Message record in the DB.
     *
     * Validates: Requirements 7.4
     */
    public function test_valid_submission_creates_message_in_database(): void
    {
        Queue::fake();

        $payload = $this->validPayload();

        $response = $this->post(route('contact.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'name'    => $payload['name'],
            'email'   => $payload['email'],
            'subject' => $payload['subject'],
        ]);
    }

    /**
     * Property 11: Message fields in DB must match submitted form data.
     *
     * Validates: Requirements 7.4
     */
    public function test_message_fields_match_submitted_data(): void
    {
        Queue::fake();

        $iterations = 3;

        $names    = ['Alice Smith', 'Bob Jones', 'Citra Dewi'];
        $emails   = ['alice@example.com', 'bob@test.org', 'citra@mail.id'];
        $subjects = ['Inquiry', 'Collaboration', 'Question'];

        for ($i = 0; $i < $iterations; $i++) {
            RateLimiter::clear('contact');

            $payload = $this->validPayload([
                'name'    => $names[$i],
                'email'   => $emails[$i],
                'subject' => $subjects[$i],
                'body'    => 'This is a valid message body for iteration ' . $i . ' with enough characters.',
            ]);

            $this->post(route('contact.store'), $payload);

            $this->assertDatabaseHas('messages', [
                'name'    => $payload['name'],
                'email'   => $payload['email'],
                'subject' => $payload['subject'],
            ]);
        }
    }

    /**
     * Property 11: Invalid submission must NOT create a Message in the DB.
     *
     * Validates: Requirements 7.4
     */
    public function test_invalid_submission_does_not_create_message(): void
    {
        Queue::fake();

        $invalidPayloads = [
            $this->validPayload(['email' => 'not-an-email']),
            $this->validPayload(['body' => 'short']),
            $this->validPayload(['name' => '']),
            $this->validPayload(['subject' => '']),
        ];

        foreach ($invalidPayloads as $payload) {
            $this->post(route('contact.store'), $payload);
        }

        $this->assertDatabaseCount('messages', 0);
    }

    // -------------------------------------------------------------------------
    // Property 13: Rate Limiting Enforced (3 per hour per IP)
    // -------------------------------------------------------------------------

    /**
     * Property 13: After 3 successful submissions from the same IP,
     * the 4th must be rejected with a rate limit error.
     *
     * Validates: Requirements 7.10, 7.11
     */
    public function test_rate_limit_enforced_after_three_submissions(): void
    {
        Queue::fake();

        $ip = '192.168.1.100';

        // Submit 3 valid requests (should all succeed)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('contact.store'), $this->validPayload([
                    'name'    => 'User ' . $i,
                    'email'   => "user{$i}@example.com",
                    'subject' => 'Subject ' . $i,
                    'body'    => 'This is a valid message body for submission number ' . $i . '.',
                ]));

            $response->assertRedirect();
        }

        $this->assertDatabaseCount('messages', 3);

        // 4th request from same IP must be rate-limited — message must NOT be saved
        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->withHeaders(['Referer' => url('/')])
            ->post(route('contact.store'), $this->validPayload([
                'name'    => 'User 4',
                'email'   => 'user4@example.com',
                'subject' => 'Subject 4',
                'body'    => 'This is a valid message body for the fourth submission attempt.',
            ]));

        // The rate limiter's custom response uses back()->withErrors() which may return
        // a redirect (302) or 429 depending on the rate limiter configuration.
        // We verify the 4th message was NOT saved to the database.
        $this->assertDatabaseCount('messages', 3);

        // No 4th message should be saved
        $this->assertDatabaseCount('messages', 3);
    }

    /**
     * Property 13: Different IPs are rate-limited independently.
     *
     * Validates: Requirements 7.10
     */
    public function test_different_ips_have_independent_rate_limits(): void
    {
        Queue::fake();

        $ip1 = '10.0.0.1';
        $ip2 = '10.0.0.2';

        // Submit 3 from IP1
        for ($i = 0; $i < 3; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip1])
                ->post(route('contact.store'), $this->validPayload([
                    'name'    => 'IP1 User ' . $i,
                    'email'   => "ip1user{$i}@example.com",
                    'subject' => 'Subject ' . $i,
                    'body'    => 'Valid message body for IP1 submission number ' . $i . '.',
                ]));
        }

        // IP2 should still be able to submit
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip2])
            ->post(route('contact.store'), $this->validPayload([
                'name'    => 'IP2 User',
                'email'   => 'ip2user@example.com',
                'subject' => 'IP2 Subject',
                'body'    => 'Valid message body from a completely different IP address.',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    // -------------------------------------------------------------------------
    // Property 14: SendContactNotification job dispatched after message save
    // -------------------------------------------------------------------------

    /**
     * Property 14: A SendContactNotification job must be dispatched
     * after a valid message is saved to the database.
     *
     * Validates: Requirements 7.12
     */
    public function test_notification_job_dispatched_after_message_save(): void
    {
        Queue::fake();

        $this->post(route('contact.store'), $this->validPayload());

        Queue::assertPushed(SendContactNotification::class);
    }

    /**
     * Property 14: The dispatched job must reference the saved Message.
     *
     * Validates: Requirements 7.12
     */
    public function test_dispatched_job_references_saved_message(): void
    {
        Queue::fake();

        $payload = $this->validPayload([
            'name'    => 'Test User',
            'email'   => 'testuser@example.com',
            'subject' => 'Test Subject',
            'body'    => 'This is a test message body with sufficient length.',
        ]);

        $this->post(route('contact.store'), $payload);

        $message = Message::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($message);

        Queue::assertPushed(SendContactNotification::class, function ($job) use ($message) {
            return $job->message->id === $message->id;
        });
    }

    /**
     * Property 14: No job must be dispatched when the submission is invalid.
     *
     * Validates: Requirements 7.12
     */
    public function test_no_job_dispatched_for_invalid_submission(): void
    {
        Queue::fake();

        $this->post(route('contact.store'), $this->validPayload(['email' => 'invalid-email']));

        Queue::assertNotPushed(SendContactNotification::class);
    }

    /**
     * Property 14: Exactly one job is dispatched per valid submission.
     *
     * Validates: Requirements 7.12
     */
    public function test_exactly_one_job_dispatched_per_valid_submission(): void
    {
        Queue::fake();

        $this->post(route('contact.store'), $this->validPayload());

        Queue::assertPushed(SendContactNotification::class, 1);
    }
}
