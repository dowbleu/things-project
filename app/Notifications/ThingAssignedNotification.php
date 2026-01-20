<?php

namespace App\Notifications;

use App\Models\Thing;
use App\Models\Usage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ThingAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Thing $thing;
    public Usage $usage;
    public $assignedUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(Thing $thing, Usage $usage, $assignedUser = null)
    {
        $this->thing = $thing;
        $this->usage = $usage;
        $this->assignedUser = $assignedUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Возвращаем только database канал
        // Email отправляется через Job асинхронно
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Этот метод не используется, так как отправка идет через ThingAssignedMail
        return (new MailMessage)
                    ->subject('Назначена вещь: ' . $this->thing->name);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Загружаем отношения, если они не загружены
        $this->usage->loadMissing(['unit', 'place']);
        
        return [
            'thing_id' => $this->thing->id,
            'thing_name' => $this->thing->name,
            'usage_id' => $this->usage->id,
            'amount' => $this->usage->amount,
            'place_name' => $this->usage->place?->name ?? 'Не указано',
            'unit_name' => $this->usage->unit?->name,
        ];
    }
}
