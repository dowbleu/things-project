<?php

namespace App\Http\Controllers;

use App\Models\ArchivedThing;
use App\Models\Thing;
use App\Models\Usage;
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
        return view('archived.index', compact('archivedThings'));
    }

    /**
     * Restore an archived thing.
     */
    public function restore(ArchivedThing $archivedThing)
    {
        if ($archivedThing->is_restored) {
            return redirect()->route('archived.index')
                ->with('error', 'Эта вещь уже была восстановлена.');
        }

        // Создаем новую вещь из архива
        $thing = Thing::create([
            'name' => $archivedThing->thing_name,
            'description' => $archivedThing->current_description,
            'master' => Auth::id(), // Восстановивший становится хозяином
        ]);

        // Помечаем архивную запись как восстановленную
        $archivedThing->update([
            'is_restored' => true,
            'restored_by' => Auth::id(),
            'restored_at' => now(),
        ]);

        return redirect()->route('archived.index')
            ->with('success', 'Вещь успешно восстановлена.');
    }
}

