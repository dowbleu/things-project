<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Назначена вещь</title>
</head>
<body>
    <h2>Назначена вещь</h2>
    <p>Здравствуйте!</p>
    <p>Пользователю <strong>{{ $assignedUser->name }}</strong> была назначена вещь: <strong>{{ $thing->name }}</strong></p>
    <p><strong>Владелец вещи:</strong> {{ $thing->masterUser->name }}</p>
    <p><strong>Количество:</strong> {{ $usage->amount }} {{ $unitText }}</p>
    <p><strong>Место хранения:</strong> {{ $placeName }}</p>
    <p><a href="{{ $appUrl }}{{ route('things.show', $thing, false) }}">Посмотреть вещь</a></p>
    <p>Спасибо за использование нашего приложения!</p>
</body>
</html>

