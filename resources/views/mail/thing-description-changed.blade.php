<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Изменено описание вещи</title>
</head>
<body>
    <h2>Изменено описание вещи</h2>
    <p>Здравствуйте!</p>
    <p>Было изменено описание вещи: <strong>{{ $thing->name }}</strong></p>
    <p><strong>Владелец вещи:</strong> {{ $thing->masterUser->name }}</p>
    <p><strong>Старое описание:</strong> {{ $oldDescription ?: 'Не было указано' }}</p>
    <p><strong>Новое описание:</strong> {{ $newDescription ?: 'Не указано' }}</p>
    <p><a href="{{ $appUrl }}{{ route('things.show', $thing, false) }}">Посмотреть вещь</a></p>
    <p>Спасибо за использование нашего приложения!</p>
</body>
</html>

