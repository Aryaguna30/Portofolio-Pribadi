<?php

// Feature: portfolio-website, Property 25: Message Read Status Update

namespace Tests\Property;

use App\Http\Controllers\Admin\MessageController;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Inertia\Support\Header;
use Tests\TestCase;

/**
 * Property 25: Message Read Status Update
 *
 * Validates: Requirements 12.3
 *
 * Property: Any Message record with is_read=false must have is_read set to true
 * and read_at set to a non-null timestamp after admin accesses the message detail.
 *
 * Tests MessageController::show() directly with 5 iterations.
 */
class MessagePropertyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    /**
     * Create an unread message with given attributes.
     */
    private function createUnreadMessage(array $overrides = []): Message
    {
        return Message::create(array_merge([
            'name'    => 'Test Sender',
            'email'   => 'sender@example.com',
            'subject' => 'Test Subject',
            'body'    => 'This is a test message body.',
            'is_read' => false,
            'read_at' => null,
        ], $overrides));
    }

    /**
     * Property 25: Any message with is_read=false must become true after admin accesses it.
     *
     * Validates: Requirements 12.3
     */
    public function test_unread_message_becomes_read_after_admin_access(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            $message = $this->createUnreadMessage([
                'name'    => "Sender {$i}",
                'email'   => "sender{$i}@example.com",
                'subject' => "Subject {$i}",
                'body'    => "Message body for iteration {$i} with enough content.",
            ]);

            $this->assertFalse(
                $message->is_read,
                "Iteration {$i}: message must start as unread"
            );
            $this->assertNull(
                $message->read_at,
                "Iteration {$i}: read_at must be null before access"
            );

            // Access the message via the controller
            $this->actingAs($this->admin)
                ->get(route('admin.messages.show', $message));

            $message->refresh();

            $this->assertTrue(
                $message->is_read,
                "Iteration {$i}: is_read must be true after admin accesses the message"
            );

            $this->assertNotNull(
                $message->read_at,
                "Iteration {$i}: read_at must be set after admin accesses the message"
            );
        }
    }

    /**
     * Property 25: Already-read messages remain read after re-access.
     *
     * Validates: Requirements 12.3
     */
    public function test_already_read_message_remains_read_after_re_access(): void
    {
        $readAt  = now()->subHour();
        $message = Message::create([
            'name'    => 'Already Read',
            'email'   => 'read@example.com',
            'subject' => 'Already Read Subject',
            'body'    => 'This message was already read before.',
            'is_read' => true,
            'read_at' => $readAt,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.messages.show', $message));

        $message->refresh();

        $this->assertTrue($message->is_read, 'Already-read message must remain read');
        $this->assertNotNull($message->read_at, 'read_at must remain set');
    }

    /**
     * Property 25: read_at timestamp must be set to approximately now() on first access.
     *
     * Validates: Requirements 12.3
     */
    public function test_read_at_is_set_to_current_time_on_access(): void
    {
        $before  = now()->subSecond();
        $message = $this->createUnreadMessage();

        $this->actingAs($this->admin)
            ->get(route('admin.messages.show', $message));

        $message->refresh();
        $after = now()->addSecond();

        $this->assertTrue(
            $message->read_at->between($before, $after),
            'read_at must be set to approximately the current time'
        );
    }
}
