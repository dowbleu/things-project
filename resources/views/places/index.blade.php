@extends('layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Места хранения</h2>
    @can('create', App\Models\Place::class)
        <a href="{{ route('places.create') }}" class="btn btn-primary">Создать место</a>
    @endcan
</div>

@if($places->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Название</th>
                <th scope="col">Описание</th>
                <th scope="col">Ремонт/Мойка</th>
                <th scope="col">В работе</th>
                <th scope="col">Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($places as $place)
                <tr>
                    <th scope="row">{{ $place->id }}</th>
                    <td><a href="{{ route('places.show', $place) }}">{{ $place->name }}</a></td>
                    <td>{{ Str::limit($place->description, 50) }}</td>
                    <td>
                        @if($place->repair)
                            <span class="badge bg-warning text-dark">Да</span>
                        @else
                            <span class="badge bg-secondary">Нет</span>
                        @endif
                    </td>
                    <td>
                        @if($place->work)
                            <span class="badge bg-success">Да</span>
                        @else
                            <span class="badge bg-secondary">Нет</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('places.show', $place) }}" class="btn btn-sm btn-info">Просмотр</a>
                        @can('update', $place)
                            <a href="{{ route('places.edit', $place) }}" class="btn btn-sm btn-warning">Редактировать</a>
                        @endcan
                        @can('delete', $place)
                            <form action="{{ route('places.destroy', $place) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить это место?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $places->links() }}
@else
    <p class="text-muted">Места еще не созданы.</p>
@endif
@endsection
