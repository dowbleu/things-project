@extends('layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Вещи</h2>
    @can('create', App\Models\Thing::class)
        <a href="{{ route('things.create') }}" class="btn btn-primary">Создать вещь</a>
    @endcan
</div>

@if($things->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Название</th>
                <th scope="col">Описание</th>
                <th scope="col">Гарантия</th>
                <th scope="col">Владелец</th>
                <th scope="col">Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($things as $thing)
                <tr @highlightUserThing($thing) @highlightPlaceThing($thing)>
                    <th scope="row">{{ $thing->id }}</th>
                    <td><a href="{{ route('things.show', $thing) }}">{{ $thing->name }}</a></td>
                    <td>{{ Str::limit($thing->currentDescription ?: ($thing->description ?: 'Не указано'), 50) }}</td>
                    <td>{{ $thing->wrnt ? $thing->wrnt->format('d.m.Y') : '-' }}</td>
                    <td>{{ $thing->masterUser->name }}</td>
                    <td>
                        <a href="{{ route('things.show', $thing) }}" class="btn btn-sm btn-info">Просмотр</a>
                        @php
                            $currentUser = Auth::user();
                        @endphp
                        @if($thing->master === Auth::id() || ($currentUser && $currentUser->isAdmin()))
                            <a href="{{ route('things.transfer', $thing) }}" class="btn btn-sm btn-success">Передать</a>
                            <a href="{{ route('things.edit', $thing) }}" class="btn btn-sm btn-warning">Редактировать</a>
                            <form action="{{ route('things.destroy', $thing) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту вещь?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $things->links() }}
@else
    <p class="text-muted">Вещи еще не созданы.</p>
@endif
@endsection
