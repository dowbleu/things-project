<?php

namespace App\Jobs;

use App\Models\Thing;
use App\Models\User;
use App\Models\Usage;
use App\Mail\ThingAssignedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendThingAssignedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Thing $thing;
    public User $user;
    public Usage $usage;

    /**
     * Create a new job instance.
     */
    public function __construct(Thing $thing, User $user, Usage $usage)
    {
        $this->thing = $thing;
        $this->user = $user;
        $this->usage = $usage;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Загружаем отношения перед отправкой email
        $this->usage->loadMissing(['unit', 'place', 'thing']);
        $this->thing->loadMissing(['masterUser']);
        
        // Отправляем только email (уведомление в БД уже отправлено синхронно в контроллере)
        $fixedEmail = env('MAIL_FROM_ADDRESS', 'laravel-dowbleu@mail.ru');
        try {
            Mail::to($fixedEmail)->send(new ThingAssignedMail($this->thing, $this->usage, $this->user));
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            Log::error('Failed to send thing assigned email: ' . $e->getMessage());
        }
    }
}
