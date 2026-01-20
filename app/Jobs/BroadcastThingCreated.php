<?php

namespace App\Jobs;

use App\Models\Thing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BroadcastThingCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $thingId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $thingId)
    {
        $this->thingId = $thingId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Загружаем модель заново по ID, чтобы избежать проблем с сериализацией
        $thing = Thing::with('masterUser')->findOrFail($this->thingId);
        
        // Вещаем событие напрямую через broadcast() helper
        try {
            broadcast(new \App\Events\ThingCreated($thing));
        } catch (\Exception $e) {
            Log::error('Failed to broadcast ThingCreated: ' . $e->getMessage());
        }
    }
}

