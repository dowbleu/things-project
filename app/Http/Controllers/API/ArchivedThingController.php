<?php

namespace App\Http\Controllers\API;

use App\Models\ArchivedThing;
use App\Models\Thing;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchivedThingController extends Controller
{
    /**
     * Display a listing of archived things.
     */
    public function index()
    {
        $archivedThings = ArchivedThing::with('restorer')
            ->latest()
            ->paginate(15);
        return response()->json($archivedThings);
    }

    /**
     * Restore an archived thing.
     */
    public function restore(ArchivedThing $archivedThing)
    {
        if ($archivedThing->is_restored) {
            return response()->json(['message' => 'Эта вещь уже была восстановлена.'], 400);
        }

        // Создаем новую вещь из архива
        $thing = Thing::create([
            'name' => $archivedThing->thing_name,
            'description' => $archivedThing->current_description,
            'master' => Auth::id(), // Восстановивший становится хозяином
        ]);

        $thing->load(['masterUser', 'usages.place', 'usages.user', 'usages.unit', 'descriptions']);

        // Помечаем архивную запись как восстановленную
        $archivedThing->update([
            'is_restored' => true,
            'restored_by' => Auth::id(),
            'restored_at' => now(),
        ]);

        return response()->json([
            'message' => 'Вещь успешно восстановлена.',
            'thing' => $thing
        ]);
    }
}

