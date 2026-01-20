<?php

namespace App\Http\Controllers\API;

use App\Events\ThingCreated;
use App\Jobs\BroadcastThingCreated;
use App\Jobs\SendThingAssignedEmail;
use App\Mail\ThingDescriptionChangedMail;
use App\Models\Place;
use App\Models\Thing;
use App\Models\ThingDescription;
use App\Models\Unit;
use App\Models\Usage;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class ThingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cacheKey = Gate::allows('view-all-things') 
            ? 'things_all_' . request()->get('page', 1)
            : 'things_user_' . Auth::id() . '_' . request()->get('page', 1);
        
        $things = Cache::remember($cacheKey, 300, function () {
            if (Gate::allows('view-all-things')) {
                return Thing::with(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions'])->orderBy('id', 'desc')->paginate(15);
            } else {
                return Thing::where('master', Auth::id())
                    ->with(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions'])
                    ->orderBy('id', 'desc')
                    ->paginate(15);
            }
        });
        
        return response()->json($things);
    }

    /**
     * Display user's own things.
     */
    public function myThings()
    {
        $things = Thing::where('master', Auth::id())
            ->with(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        return response()->json($things);
    }

    /**
     * Display things in repair places.
     */
    public function repairThings()
    {
        $things = Thing::whereHas('usages.place', function ($query) {
            $query->where('repair', true);
        })->with(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        return response()->json($things);
    }

    /**
     * Display things in work places.
     */
    public function workThings()
    {
        $things = Thing::whereHas('usages.place', function ($query) {
            $query->where('work', true);
        })->with(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        return response()->json($things);
    }

    /**
     * Display user's things that are used by other users.
     */
    public function usedThings()
    {
        $things = Thing::where('master', Auth::id())
            ->whereHas('usages', function ($query) {
                $query->where('user_id', '!=', Auth::id());
            })
            ->with(['masterUser', 'usages.user', 'usages.place', 'usages.unit', 'descriptions'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        return response()->json($things);
    }

    /**
     * Display all things.
     */
    public function allThings()
    {
        $things = Thing::with(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions'])->orderBy('id', 'desc')->paginate(15);
        return response()->json($things);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Thing::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'wrnt' => 'nullable|date',
        ]);

        $validated['master'] = Auth::id();

        $thing = Thing::create($validated);
        $thing->load(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions']);
        
        Cache::flush();
        
        // Отправляем событие создания вещи через Job
        BroadcastThingCreated::dispatch($thing->id);

        return response()->json([
            'message' => 'Вещь успешно создана.',
            'thing' => $thing
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Thing $thing)
    {
        $thing->load(['masterUser', 'usages.user', 'usages.place', 'usages.unit', 'descriptions.creator']);
        return response()->json($thing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Thing $thing)
    {
        // Проверяем через Gate
        if (!Gate::allows('update-thing', $thing)) {
            return response()->json(['message' => 'Вы не можете редактировать эту вещь.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'wrnt' => 'nullable|date',
        ]);

        $descriptionChanged = $thing->description !== $validated['description'];
        $oldDescription = $thing->description;
        
        $thing->update($validated);
        $thing->load(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions']);
        
        // Очищаем кэш вещей
        Cache::flush();

        // Отправляем уведомление на email при изменении описания через очередь
        if ($descriptionChanged) {
            $fixedEmail = env('MAIL_FROM_ADDRESS', 'laravel-dowbleu@mail.ru');
            Mail::to($fixedEmail)->queue(
                new ThingDescriptionChangedMail($thing, $oldDescription, $validated['description'])
            );
        }

        return response()->json([
            'message' => 'Вещь успешно обновлена.',
            'thing' => $thing
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Thing $thing)
    {
        // Проверяем, что пользователь является владельцем
        if ($thing->master !== Auth::id()) {
            return response()->json(['message' => 'Вы не можете удалить эту вещь.'], 403);
        }

        // Загружаем необходимые отношения
        $thing->load(['masterUser', 'descriptions']);

        // Сохраняем в архив перед удалением
        $lastUsage = $thing->usages()->latest()->first();
        $lastUser = $lastUsage ? $lastUsage->user : null;
        $lastPlace = $lastUsage ? $lastUsage->place : null;

        \App\Models\ArchivedThing::create([
            'thing_name' => $thing->name,
            'current_description' => $thing->currentDescription,
            'master_name' => $thing->masterUser->name,
            'last_user_name' => $lastUser ? $lastUser->name : null,
            'place_name' => $lastPlace ? $lastPlace->name : null,
            'is_restored' => false,
        ]);

        $thing->delete();

        Cache::flush();

        return response()->json(['message' => 'Вещь успешно удалена и сохранена в архив.']);
    }

    /**
     * Сохранить передачу вещи пользователю
     */
    public function transfer(Request $request, Thing $thing)
    {
        // Проверяем, что пользователь является владельцем или админом
        /** @var User|null $user */
        $user = Auth::user();
        if ($thing->master !== Auth::id() && (!$user || $user->role !== 'admin')) {
            return response()->json(['message' => 'Вы можете передавать только свои вещи.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'place_id' => 'required|exists:places,id',
            'amount' => 'required|integer|min:1',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        // Проверяем уникальность комбинации thing_id, place_id, user_id
        $existingUsage = Usage::where('thing_id', $thing->id)
            ->where('place_id', $validated['place_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        $user = User::findOrFail($validated['user_id']);
        
        if ($existingUsage) {
            // Обновляем существующее использование
            $existingUsage->update([
                'amount' => $validated['amount'],
                'unit_id' => $validated['unit_id'] ?? null,
            ]);
            $usage = $existingUsage;
        } else {
            // Создаем новое использование
            $usage = Usage::create([
                'thing_id' => $thing->id,
                'user_id' => $validated['user_id'],
                'place_id' => $validated['place_id'],
                'amount' => $validated['amount'],
                'unit_id' => $validated['unit_id'] ?? null,
            ]);
        }

        $usage->load(['place', 'user', 'unit']);

        // Отправляем email через очередь
        SendThingAssignedEmail::dispatch($thing, $user, $usage);
        
        // Отправляем уведомление в БД синхронно
        $user->notify(new \App\Notifications\ThingAssignedNotification($thing, $usage, $user));

        return response()->json([
            'message' => 'Вещь успешно передана пользователю ' . $user->name . '. Уведомление отправлено.',
            'usage' => $usage
        ]);
    }

    /**
     * Добавить новое описание к вещи
     */
    public function storeDescription(Request $request, Thing $thing)
    {
        $request->validate([
            'description' => 'required|string',
            'is_current' => 'boolean',
        ]);

        // Если устанавливаем как актуальное, снимаем флаг с других
        if ($request->has('is_current') && $request->is_current) {
            ThingDescription::where('thing_id', $thing->id)
                ->update(['is_current' => false]);
        }

        $description = ThingDescription::create([
            'thing_id' => $thing->id,
            'description' => $request->description,
            'is_current' => $request->has('is_current') && $request->is_current,
            'created_by' => Auth::id(),
        ]);

        $description->load('creator');

        // Отправляем уведомление хозяину вещи через очередь
        if ($thing->masterUser) {
            $fixedEmail = env('MAIL_FROM_ADDRESS', 'laravel-dowbleu@mail.ru');
            Mail::to($fixedEmail)->queue(
                new ThingDescriptionChangedMail($thing, $thing->description, $request->description)
            );
        }

        return response()->json([
            'message' => 'Описание успешно добавлено.',
            'description' => $description
        ], 201);
    }

    /**
     * Установить описание как актуальное
     */
    public function setCurrentDescription(Request $request, Thing $thing, ThingDescription $description)
    {
        // Снимаем флаг актуальности со всех описаний
        ThingDescription::where('thing_id', $thing->id)
            ->update(['is_current' => false]);

        // Устанавливаем выбранное как актуальное
        $description->update(['is_current' => true]);
        $description->load('creator');

        // Отправляем уведомление через очередь
        if ($thing->masterUser) {
            $fixedEmail = env('MAIL_FROM_ADDRESS', 'laravel-dowbleu@mail.ru');
            Mail::to($fixedEmail)->queue(
                new ThingDescriptionChangedMail($thing, $thing->description, $description->description)
            );
        }

        return response()->json([
            'message' => 'Актуальное описание изменено.',
            'description' => $description
        ]);
    }
}

