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
    <body class="bg-duo-surface font-sans text-duo-ink">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="/" wire:navigate class="mb-8 flex items-center gap-3">
                <x-duo-owl class="h-14 w-14" />
                <span class="text-4xl font-extrabold lowercase tracking-tight text-duo-green">duoLaravel</span>
            </a>

            <div class="w-full max-w-md rounded-2xl border-2 border-duo-border bg-duo-card p-6 sm:p-8">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-sm font-bold text-duo-silver">
                Aprende Laravel con lecciones cortas, como un idioma. 🦉
            </p>
        </div>
    </body>
</html>
