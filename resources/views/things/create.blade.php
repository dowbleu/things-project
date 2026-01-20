@extends('layout')
@section('content')

<ul class="list-group mb-3">
    @foreach($errors->all() as $error)
        <li class="list-group-item list-group-item-danger">{{ $error }}</li>
    @endforeach
</ul>

<form action="{{ route('things.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Название</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Описание</label>
        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
    </div>
    <div class="mb-3">
        <label for="wrnt" class="form-label">Гарантия/Срок годности</label>
        <input type="date" class="form-control" id="wrnt" name="wrnt" value="{{ old('wrnt') }}">
    </div>
    <button type="submit" class="btn btn-primary">Создать вещь</button>
    <a href="{{ route('things.index') }}" class="btn btn-secondary">Отмена</a>
</form>
@endsection
