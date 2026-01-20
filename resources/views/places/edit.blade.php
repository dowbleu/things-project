@extends('layout')
@section('content')

<ul class="list-group mb-3">
    @foreach($errors->all() as $error)
        <li class="list-group-item list-group-item-danger">{{ $error }}</li>
    @endforeach
</ul>

<form action="{{ route('places.update', $place) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label for="name" class="form-label">Название</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $place->name) }}" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Описание</label>
        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $place->description) }}</textarea>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="repair" name="repair" value="1" {{ old('repair', $place->repair) ? 'checked' : '' }}>
        <label class="form-check-label" for="repair">Ремонт/Мойка (специальное место пребывания)</label>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="work" name="work" value="1" {{ old('work', $place->work) ? 'checked' : '' }}>
        <label class="form-check-label" for="work">Находится в работе</label>
    </div>
    <button type="submit" class="btn btn-primary">Обновить место</button>
    <a href="{{ route('places.index') }}" class="btn btn-secondary">Отмена</a>
</form>
@endsection
