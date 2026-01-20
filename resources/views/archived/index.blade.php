@extends('layout')
@section('content')

<h2>Архив удаленных вещей</h2>

@if($archivedThings->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Название</th>
                <th scope="col">Описание</th>
                <th scope="col">Хозяин</th>
                <th scope="col">Последний пользователь</th>
                <th scope="col">Место хранения</th>
                <th scope="col">Статус</th>
                <th scope="col">Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($archivedThings as $archived)
                <tr>
                    <td>{{ $archived->thing_name }}</td>
                    <td>{{ $archived->current_description ?: 'Не указано' }}</td>
                    <td>{{ $archived->master_name }}</td>
                    <td>{{ $archived->last_user_name ?: '-' }}</td>
                    <td>{{ $archived->place_name ?: '-' }}</td>
                    <td>
                        @if($archived->is_restored)
                            <span class="badge bg-success">Восстановлено</span>
                            <br><small>Восстановил: {{ $archived->restorer->name ?? '-' }}</small>
                            <br><small>{{ $archived->restored_at ? $archived->restored_at->format('d.m.Y H:i') : '-' }}</small>
                        @else
                            <span class="badge bg-secondary">Не восстановлено</span>
                        @endif
                    </td>
                    <td>
                        @if(!$archived->is_restored)
                            <form action="{{ route('archived.restore', $archived) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите восстановить эту вещь?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Восстановить</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $archivedThings->links() }}
@else
    <p class="text-muted">Архив пуст.</p>
@endif
@endsection

