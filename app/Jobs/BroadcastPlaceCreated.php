<?php

namespace App\Jobs;

use App\Models\Place;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BroadcastPlaceCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $placeId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $placeId)
    {
        $this->placeId = $placeId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Загружаем модель заново по ID, чтобы избежать проблем с сериализацией
        $place = Place::findOrFail($this->placeId);
        
        // Вещаем событие напрямую через broadcast() helper
        try {
            broadcast(new \App\Events\PlaceCreated($place));
        } catch (\Exception $e) {
            Log::error('Failed to broadcast PlaceCreated: ' . $e->getMessage());
        }
    }
}

