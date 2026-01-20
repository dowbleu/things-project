<?php

namespace App\Http\Controllers\API;

use App\Events\PlaceCreated;
use App\Jobs\BroadcastPlaceCreated;
use App\Models\Place;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Place::class);
        $cacheKey = 'places_v2_' . request()->get('page', 1);
        $places = Cache::remember($cacheKey, 300, function () {
            return Place::orderBy('id', 'desc')->paginate(15);
        });
        return response()->json($places);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Place::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'repair' => 'boolean',
            'work' => 'boolean',
        ]);

        // Убеждаемся, что boolean поля установлены
        $validated['repair'] = $request->has('repair');
        $validated['work'] = $request->has('work');

        $place = Place::create($validated);
        
        // Очищаем кэш мест
        Cache::flush();
        
        // Отправляем событие создания места через Job
        BroadcastPlaceCreated::dispatch($place->id);

        return response()->json([
            'message' => 'Место успешно создано.',
            'place' => $place
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Place $place)
    {
        $this->authorize('view', $place);
        $place->load(['usages.thing', 'usages.user', 'usages.unit']);
        return response()->json($place);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Place $place)
    {
        $this->authorize('update', $place);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'repair' => 'boolean',
            'work' => 'boolean',
        ]);

        // Убеждаемся, что boolean поля установлены
        $validated['repair'] = $request->has('repair');
        $validated['work'] = $request->has('work');

        $place->update($validated);
        
        // Очищаем кэш мест
        Cache::flush();

        return response()->json([
            'message' => 'Место успешно обновлено.',
            'place' => $place
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Place $place)
    {
        $this->authorize('delete', $place);
        $place->delete();
        
        // Очищаем кэш мест
        Cache::flush();

        return response()->json(['message' => 'Место успешно удалено.']);
    }
}

