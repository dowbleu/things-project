<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>Things Project</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Things Project</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle @activeTab('things.*')" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Вещи
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item @activeTab('things.index')" href="{{ route('things.index') }}">Все вещи</a></li>
                                    <li><a class="dropdown-item @activeTab('things.my')" href="{{ route('things.my') }}">Мои вещи</a></li>
                                    <li><a class="dropdown-item @activeTab('things.repair')" href="{{ route('things.repair') }}">Вещи в ремонте/мойке</a></li>
                                    <li><a class="dropdown-item @activeTab('things.work')" href="{{ route('things.work') }}">Вещи в работе</a></li>
                                    <li><a class="dropdown-item @activeTab('things.used')" href="{{ route('things.used') }}">Мои вещи, используемые другими</a></li>
                                    <li><a class="dropdown-item @activeTab('things.all')" href="{{ route('things.all') }}">Общий список</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @activeTab('places.*')" href="{{ route('places.index') }}">Места</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @activeTab('archived.*')" href="{{ route('archived.index') }}">Архив</a>
                            </li>
                            @can('create', App\Models\Thing::class)
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('things.create') }}">Создать вещь</a>
                                </li>
                            @endcan
                        @endauth
                    </ul>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        @auth
                            @php
                                $thingNotifications = auth()->user()->unreadNotifications->filter(function ($notify) {
                                    return $notify->type === 'App\Notifications\ThingAssignedNotification';
                                });
                            @endphp
                            @if($thingNotifications->count() > 0)
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Уведомления <span class="badge bg-danger">{{ $thingNotifications->count() }}</span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        @foreach($thingNotifications as $notify)
                                            @if(isset($notify->data['thing_id']) && isset($notify->data['thing_name']))
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('things.show', ['thing' => $notify->data['thing_id'], 'notify' => $notify->id]) }}">
                                                        Вам передана вещь: {{ $notify->data['thing_name'] }}
                                                        @if(isset($notify->data['place_name']))
                                                            <br><small class="text-muted">Место: {{ $notify->data['place_name'] }}</small>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endauth
                        <p class="nav-item mb-0 me-2">Привет, {{ auth()->user()->name ?? 'Гость' }}</p>
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-outline-success mr-5">Войти</a>
                            <a href="{{ route('signin') }}" class="btn btn-outline-success ml-3">Регистрация</a>
                        @endguest
                        @auth
                            <a href="/auth/logout" class="btn btn-outline-success">Выход</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <div class="container mt-5">
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </header>
</body>

</html>

