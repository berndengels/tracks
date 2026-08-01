<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <base href="/">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="cache-control" content="max-age=0">
    <meta http-equiv="cache-control" content="no-cache">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preload" as="font">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}?{{ time() }}">
    @stack('styles')
    <script src="{{ mix('js/app.js') }}?{{ time() }}"></script>
    @stack('scripts')
</head>
<body>
    <div class="grid-container">
        @auth
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Navbar</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('admin.tracks.index') }}">Tracks</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.media.index') }}">Media</a>
                            </li>
                        </ul>
                    </div>
                    <div class="float-end m-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-responsive-nav-link :href="route('logout')"
                                                   :active="true"
                                                   onclick="event.preventDefault();
                                        this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
            </nav>
        @endauth
        @yield('main')
    </div>
    @stack('inline-scripts')
</body>
</html>
