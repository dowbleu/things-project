<?php

namespace App\Http\Controllers;

use App\Models\Thing;
use App\Models\ThingDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ThingDescriptionChangedMail;

class ThingDescriptionController extends Controller
{
    /**
     * Добавить новое описание к вещи
     */
    public function store(Request $request, Thing $thing)
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

        // Отправляем уведомление хозяину вещи через очередь
        if ($thing->masterUser) {
            $fixedEmail = env('MAIL_FROM_ADDRESS', 'laravel-dowbleu@mail.ru');
            Mail::to($fixedEmail)->queue(
                new ThingDescriptionChangedMail($thing, $thing->description, $request->description)
            );
        }

        return redirect()->route('things.show', $thing)
            ->with('success', 'Описание успешно добавлено.');
    }

    /**
     * Установить описание как актуальное
     */
    public function setCurrent(Request $request, Thing $thing, ThingDescription $description)
    {
        // Снимаем флаг актуальности со всех описаний
        ThingDescription::where('thing_id', $thing->id)
            ->update(['is_current' => false]);

        // Устанавливаем выбранное как актуальное
        $description->update(['is_current' => true]);

        // Отправляем уведомление через очередь
        if ($thing->masterUser) {
            $fixedEmail = env('MAIL_FROM_ADDRESS', 'laravel-dowbleu@mail.ru');
            Mail::to($fixedEmail)->queue(
                new ThingDescriptionChangedMail($thing, $thing->description, $description->description)
            );
        }

        return redirect()->route('things.show', $thing)
            ->with('success', 'Актуальное описание изменено.');
    }
}

