<?php

namespace App\Http\Controllers;

use App\Events\ThingCreated;
use App\Jobs\BroadcastThingCreated;
use App\Jobs\SendThingAssignedEmail;
use App\Mail\ThingDescriptionChangedMail;
use App\Models\Place;
use App\Models\Thing;
use App\Models\Unit;
use App\Models\Usage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
                return Thing::with(['masterUser', 'usages.place', 'descriptions'])->orderBy('id', 'desc')->paginate(15);
            } else {
                return Thing::where('master', Auth::id())
                    ->with(['masterUser', 'usages.place', 'descriptions'])
                    ->orderBy('id', 'desc')
                    ->paginate(15);
            }
        });
        
        return view('things.index', compact('things'));
    }

    /**
     * Display user's own things.
     */
    public function myThings()
    {
        $things = Thing::where('master', Auth::id())
            ->with(['masterUser', 'usages.place', 'descriptions'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        $title = 'Мои вещи';
        return view('things.list', compact('things', 'title'));
    }

    /**
     * Display things in repair places.
     */
    public function repairThings()
    {
        $things = Thing::whereHas('usages.place', function ($query) {
            $query->where('repair', true);
        })->with(['masterUser', 'usages.place', 'descriptions'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        $title = 'Вещи в ремонте/мойке';
        return view('things.list', compact('things', 'title'));
    }

    /**
     * Display things in work places.
     */
    public function workThings()
    {
        $things = Thing::whereHas('usages.place', function ($query) {
            $query->where('work', true);
        })->with(['masterUser', 'usages.place', 'descriptions'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        $title = 'Вещи в работе';
        return view('things.list', compact('things', 'title'));
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
            ->with(['masterUser', 'usages.user', 'usages.place', 'descriptions'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        $title = 'Мои вещи, используемые другими';
        return view('things.list', compact('things', 'title'));
    }

    /**
     * Display all things.
     */
    public function allThings()
    {
        $things = Thing::with(['masterUser', 'usages.place', 'descriptions'])->orderBy('id', 'desc')->paginate(15);
        $title = 'Все вещи';
        return view('things.list', compact('things', 'title'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Thing::class);
        return view('things.create');
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
        
        Cache::flush();
        
        // Отправляем событие создания вещи через Job (передаем только ID для избежания проблем с сериализацией)
        BroadcastThingCreated::dispatch($thing->id);

        return redirect()->route('things.index')
            ->with('success', 'Вещь успешно создана.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Thing $thing, Request $request)
    {
        // Помечаем уведомление как прочитанное, если оно передано в запросе
        if ($request->has('notify') && Auth::check()) {
            $notification = Auth::user()->notifications->where('id', $request->notify)->first();
            if ($notification && $notification->type === 'App\Notifications\ThingAssignedNotification') {
                $notification->markAsRead();
            }
        }
        
        $thing->load(['masterUser', 'usages.user', 'usages.place', 'usages.unit', 'descriptions.creator']);
        return view('things.show', compact('thing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Thing $thing)
    {
        // Проверяем через Gate
        if (!Gate::allows('update-thing', $thing)) {
            abort(403, 'Вы не можете редактировать эту вещь.');
        }

        return view('things.edit', compact('thing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Thing $thing)
    {
        // Проверяем через Gate
        if (!Gate::allows('update-thing', $thing)) {
            abort(403, 'Вы не можете редактировать эту вещь.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'wrnt' => 'nullable|date',
        ]);

        $descriptionChanged = $thing->description !== $validated['description'];
        $oldDescription = $thing->description;
        
        $thing->update($validated);
        
        // Очищаем кэш вещей
        Cache::flush();

        // Отправляем уведомление на email при изменении описания через очередь
        if ($descriptionChanged) {
            $fixedEmail = env('MAIL_FROM_ADDRESS', 'laravel-dowbleu@mail.ru');
            Mail::to($fixedEmail)->queue(
                new ThingDescriptionChangedMail($thing, $oldDescription, $validated['description'])
            );
        }

        $redirect = redirect()->route('things.index')
            ->with('success', 'Вещь успешно обновлена.');
            
        if ($descriptionChanged) {
            $redirect->with('info', 'Описание вещи изменено. Уведомление отправлено на почту.');
        }
        
        return $redirect;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Thing $thing)
    {
        // Проверяем, что пользователь является владельцем
        if ($thing->master !== Auth::id()) {
            abort(403, 'Вы не можете удалить эту вещь.');
        }

        // Сохраняем в архив перед удалением
        $lastUsage = $thing->usages()->latest()->first();
        $lastUser = $lastUsage ? $lastUsage->user : null;
        $lastPlace = $lastUsage ? $lastUsage->place : null;

        \App\Models\ArchivedThing::create([
            'thing_name' => $thing->name,
            'current_description' => $thing->description,
            'master_name' => $thing->masterUser->name,
            'last_user_name' => $lastUser ? $lastUser->name : null,
            'place_name' => $lastPlace ? $lastPlace->name : null,
            'is_restored' => false,
        ]);

        $thing->delete();

        Cache::flush();

        return redirect()->route('things.index')
            ->with('success', 'Вещь успешно удалена и сохранена в архив.');
    }

    /**
     * Показать форму передачи вещи пользователю
     */
    public function showTransferForm(Thing $thing)
    {
        // Проверяем, что пользователь является владельцем или админом
        /** @var User|null $user */
        $user = Auth::user();
        if ($thing->master !== Auth::id() && (!$user || $user->role !== 'admin')) {
            return redirect()->route('things.show', $thing)
                ->with('error', 'Вы можете передавать только свои вещи.');
        }

        $users = User::orderBy('name')->get();
        $places = Place::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('things.transfer', compact('thing', 'users', 'places', 'units'));
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
            return redirect()->route('things.show', $thing)
                ->with('error', 'Вы можете передавать только свои вещи.');
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

        // Отправляем email через очередь
        SendThingAssignedEmail::dispatch($thing, $user, $usage);
        
        // Отправляем уведомление в БД синхронно, чтобы пользователь увидел его сразу
        // (email отправляется через очередь, а уведомление в БД - синхронно)
        $user->notify(new \App\Notifications\ThingAssignedNotification($thing, $usage, $user));
        
        // Если пользователь передает вещь самому себе
        if ($user->id === Auth::id()) {
            return redirect()->route('things.show', $thing)
                ->with('success', 'Вещь успешно передана вам! Проверьте почту для деталей.')
                ->with('info', 'Вам передана вещь: ' . $thing->name);
        }

        return redirect()->route('things.show', $thing)
            ->with('success', 'Вещь успешно передана пользователю ' . $user->name . '. Уведомление отправлено.');
    }
}
