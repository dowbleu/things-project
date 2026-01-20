@extends('layout')
@section('content')

<ul class="list-group mb-3">
    @foreach($errors->all() as $error)
        <li class="list-group-item list-group-item-danger">{{ $error }}</li>
    @endforeach
</ul>

<div class="card mb-3">
    <div class="card-body">
        <p><strong>Вещь:</strong> {{ $thing->name }}</p>
        <p><strong>Владелец:</strong> {{ $thing->masterUser->name }}</p>
    </div>
</div>

<form action="{{ route('things.transfer.store', $thing) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="user_id" class="form-label">Выберите пользователя</label>
        <select class="form-select" id="user_id" name="user_id" required>
            <option value="">-- Выберите пользователя --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label for="place_id" class="form-label">Выберите место хранения</label>
        <select class="form-select" id="place_id" name="place_id" required>
            <option value="">-- Выберите место --</option>
            @foreach($places as $place)
                <option value="{{ $place->id }}" {{ old('place_id') == $place->id ? 'selected' : '' }}>
                    {{ $place->name }}
                    @if($place->repair)
                        (Ремонт/Мойка)
                    @endif
                    @if($place->work)
                        (В работе)
                    @endif
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label for="amount" class="form-label">Количество</label>
        <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount', 1) }}" required min="1">
    </div>
    <div class="mb-3">
        <label for="unit_id" class="form-label">Размерность (необязательно)</label>
        <select class="form-select" id="unit_id" name="unit_id">
            <option value="">-- Без размерности --</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                    {{ $unit->name }} ({{ $unit->abbreviation }})
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Передать вещь</button>
    <a href="{{ route('things.show', $thing) }}" class="btn btn-secondary">Отмена</a>
</form>
@endsection
