<?php

namespace App\Http\Controllers;

use App\Events\PlaceCreated;
use App\Jobs\BroadcastPlaceCreated;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Place::class);
        // Используем новый ключ кэша для принудительного обновления
        $cacheKey = 'places_v2_' . request()->get('page', 1);
        $places = Cache::remember($cacheKey, 300, function () {
            return Place::orderBy('id', 'desc')->paginate(15);
        });
        return view('places.index', compact('places'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Place::class);
        return view('places.create');
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
        
        // Отправляем событие создания места через Job (передаем только ID для избежания проблем с сериализацией)
        BroadcastPlaceCreated::dispatch($place->id);

        return redirect()->route('places.index')
            ->with('success', 'Место успешно создано.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Place $place)
    {
        $this->authorize('view', $place);
        $place->load(['usages.thing', 'usages.user']);
        return view('places.show', compact('place'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Place $place)
    {
        $this->authorize('update', $place);
        return view('places.edit', compact('place'));
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

        return redirect()->route('places.index')
            ->with('success', 'Место успешно обновлено.');
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

        return redirect()->route('places.index')
            ->with('success', 'Место успешно удалено.');
    }
}
