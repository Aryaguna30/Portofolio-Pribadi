<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\SiteSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Message as MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendContactNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly Message $message) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adminEmail = SiteSetting::get('admin_email', config('mail.from.address'));

        if (empty($adminEmail)) {
            Log::warning('SendContactNotification: No admin email configured, skipping.');
            return;
        }

        Mail::raw(
            "New contact message from {$this->message->name} ({$this->message->email})\n\n"
            . "Subject: {$this->message->subject}\n\n"
            . $this->message->body,
            function (MailMessage $mail) use ($adminEmail) {
                $mail->to($adminEmail)
                    ->subject("New Contact: {$this->message->subject}");
            }
        );
    }
}
