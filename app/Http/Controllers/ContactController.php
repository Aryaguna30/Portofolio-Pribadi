<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Jobs\SendContactNotification;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /**
     * Store a new contact message.
     *
     * Requirements: 7.4, 7.7, 7.9, 7.12
     *
     * - Validates input via ContactFormRequest (includes honeypot check)
     * - Saves the message to the database
     * - Dispatches SendContactNotification job asynchronously
     * - Redirects back with a flash success message
     */
    public function store(ContactFormRequest $request): RedirectResponse
    {
        // Save the validated message to the database (Req 7.4)
        $message = Message::create([
            'name'       => $request->validated('name'),
            'email'      => $request->validated('email'),
            'subject'    => $request->validated('subject'),
            'body'       => $request->validated('body'),
            'ip_address' => $request->ip(),
            'is_read'    => false,
        ]);

        // Dispatch async notification job (Req 7.12)
        SendContactNotification::dispatch($message);

        // Redirect back with flash success message (Req 7.9)
        return redirect()->back()->with('success', __('messages.contact_success'));
    }
}
