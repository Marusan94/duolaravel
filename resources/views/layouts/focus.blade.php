@php
    $duoTema = request()->cookie('duo_theme');
    $duoTema = in_array($duoTema, ['clasico', 'menta', 'amanecer', 'noche', 'selva', 'uva', 'oceano'], true) ? $duoTema : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if ($duoTema) data-theme="{{ $duoTema }}" @endif>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'duoLaravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800,900" rel="stylesheet" />

        <meta name="theme-color" content="#58CC02">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        <script>
            (function () {
                try {
                    var tema = null;
                    var m = document.cookie.match(/(?:^|;\s*)duo_theme=([a-z]+)/);
                    if (m) { tema = m[1]; }
                    if (!tema) {
                        tema = localStorage.getItem('duo-theme');
                        if (tema) { document.cookie = 'duo_theme=' + tema + ';path=/;max-age=31536000;samesite=lax'; }
                    }
                    if (tema) { document.documentElement.setAttribute('data-theme', tema); }
                } catch (e) {}
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-duo-surface font-sans">
        {{ $slot }}
    </body>
</html>
