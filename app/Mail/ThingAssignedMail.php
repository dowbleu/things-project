<?php

namespace App\Mail;

use App\Models\Thing;
use App\Models\Usage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ThingAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Thing $thing;
    public Usage $usage;
    public User $assignedUser;

    /**
     * Create a new message instance.
     */
    public function __construct(Thing $thing, Usage $usage, User $assignedUser)
    {
        $this->thing = $thing;
        $this->usage = $usage;
        $this->assignedUser = $assignedUser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Назначена вещь: ' . $this->thing->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->usage->loadMissing(['unit', 'place']);
        $this->thing->loadMissing(['masterUser']);
        
        $unitText = $this->usage->unit 
            ? $this->usage->unit->name . ' (' . $this->usage->unit->abbreviation . ')' 
            : 'штук';
        
        $placeName = $this->usage->place ? $this->usage->place->name : 'Не указано';
        
        return new Content(
            html: 'mail.thing-assigned',
            with: [
                'thing' => $this->thing,
                'usage' => $this->usage,
                'assignedUser' => $this->assignedUser,
                'unitText' => $unitText,
                'placeName' => $placeName,
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
