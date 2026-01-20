<?php

namespace App\Mail;

use App\Models\Thing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ThingDescriptionChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Thing $thing;
    public $oldDescription;
    public $newDescription;

    /**
     * Create a new message instance.
     */
    public function __construct(Thing $thing, $oldDescription, $newDescription)
    {
        $this->thing = $thing;
        $this->oldDescription = $oldDescription;
        $this->newDescription = $newDescription;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Изменено описание вещи: ' . $this->thing->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->thing->loadMissing(['masterUser']);
        
        return new Content(
            html: 'mail.thing-description-changed',
            with: [
                'thing' => $this->thing,
                'oldDescription' => $this->oldDescription,
                'newDescription' => $this->newDescription,
                'appUrl' => config('app.url', 'http://127.0.0.1:3000'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

