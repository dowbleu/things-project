@extends('layout')
@section('content')

<div class="card mb-4" style="width: 100%;">
    <div class="card-body">
        <h5 class="card-title">{{ $place->name }}</h5>
        <p class="card-text"><strong>Описание:</strong> {{ $place->description ?: 'Не указано' }}</p>
        <p class="card-text"><strong>Ремонт/Мойка:</strong> {{ $place->repair ? 'Да' : 'Нет' }}</p>
        <p class="card-text"><strong>В работе:</strong> {{ $place->work ? 'Да' : 'Нет' }}</p>
        <p class="card-text"><strong>Создано:</strong> {{ $place->created_at->format('d.m.Y H:i') }}</p>
        @can('update', $place)
            <a href="{{ route('places.edit', $place) }}" class="btn btn-warning me-3">Редактировать</a>
        @endcan
        <a href="{{ route('places.index') }}" class="btn btn-secondary">Назад к списку</a>
    </div>
</div>

@if($place->usages->count() > 0)
    <h4 class="mt-4">Использования</h4>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Вещь</th>
                <th scope="col">Пользователь</th>
                <th scope="col">Количество</th>
                <th scope="col">Дата</th>
            </tr>
        </thead>
        <tbody>
            @foreach($place->usages as $usage)
                <tr>
                    <td>{{ $usage->thing->name }}</td>
                    <td>{{ $usage->user->name }}</td>
                    <td>{{ $usage->amount }}</td>
                    <td>{{ $usage->created_at->format('d.m.Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-muted">Использования не найдены.</p>
@endif
@endsection
