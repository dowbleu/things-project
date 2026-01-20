@extends('layout')
@section('content')

<div class="card mb-4" style="width: 100%;">
    <div class="card-body">
        <h5 class="card-title">{{ $thing->name }}</h5>
        <h6 class="card-subtitle mb-2 text-body-secondary">Владелец: {{ $thing->masterUser->name }}</h6>
        <p class="card-text"><strong>Текущее описание:</strong> {{ $thing->currentDescription ?: ($thing->description ?: 'Не указано') }}</p>
        
        @if($thing->descriptions->count() > 0)
            <div class="mt-3">
                <h6>История описаний:</h6>
                <ul class="list-group">
                    @foreach($thing->descriptions as $desc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1">{{ $desc->description }}</p>
                                <small class="text-muted">Создано: {{ $desc->created_at->format('d.m.Y H:i') }} 
                                    @if($desc->creator) пользователем {{ $desc->creator->name }} @endif
                                    @if($desc->is_current) <span class="badge bg-primary">Актуальное</span> @endif
                                </small>
                            </div>
                            @if(!$desc->is_current)
                                <form action="{{ route('things.descriptions.set-current', [$thing, $desc]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Сделать актуальным</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="mt-3">
            <h6>Добавить новое описание:</h6>
            <form action="{{ route('things.descriptions.store', $thing) }}" method="POST">
                @csrf
                <div class="mb-2">
                    <textarea name="description" class="form-control" rows="3" required placeholder="Введите описание"></textarea>
                </div>
                <div class="mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1">
                        <label class="form-check-label" for="is_current">
                            Сделать актуальным описанием
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Добавить описание</button>
            </form>
        </div>
        <p class="card-text"><strong>Гарантия/Срок годности:</strong> {{ $thing->wrnt ? $thing->wrnt->format('d.m.Y') : 'Не указано' }}</p>
        <p class="card-text"><strong>Создано:</strong> {{ $thing->created_at->format('d.m.Y H:i') }}</p>
        @php
            $currentUser = Auth::user();
        @endphp
        @if($thing->master === Auth::id() || ($currentUser && $currentUser->isAdmin()))
            <div class="btn-toolbar mt-3" role="toolbar">
                <a href="{{ route('things.transfer', $thing) }}" class="btn btn-success me-3">Передать</a>
                <a href="{{ route('things.edit', $thing) }}" class="btn btn-warning me-3">Редактировать</a>
            </div>
        @endif
        <a href="{{ route('things.index') }}" class="btn btn-secondary mt-2">Назад к списку</a>
    </div>
</div>

@if($thing->usages->count() > 0)
    <h4 class="mt-4">Использования</h4>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Место хранения</th>
                <th scope="col">Пользователь</th>
                <th scope="col">Количество</th>
                <th scope="col">Размерность</th>
                <th scope="col">Дата передачи</th>
            </tr>
        </thead>
        <tbody>
            @foreach($thing->usages as $usage)
                <tr>
                    <td>
                        {{ $usage->place->name }}
                        @if($usage->place->repair)
                            <span class="badge bg-warning text-dark">Ремонт/Мойка</span>
                        @endif
                        @if($usage->place->work)
                            <span class="badge bg-success">В работе</span>
                        @endif
                    </td>
                    <td>{{ $usage->user->name }}</td>
                    <td>{{ $usage->amount }}</td>
                    <td>
                        @if($usage->unit)
                            {{ $usage->unit->name }} ({{ $usage->unit->abbreviation }})
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $usage->created_at->format('d.m.Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-muted">Использования не найдены.</p>
@endif
@endsection
