<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white">
        <div class="min-h-screen flex flex-col justify-center items-center py-10 bg-slate-50">
            <div class="w-full sm:max-w-2xl px-6">
                <div class="rounded-[32px] border border-gray-200 bg-white p-8 shadow-xl">
                    <div class="flex flex-col items-center text-center gap-3 mb-8">
                        <div class="h-16 w-16 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold">A</div>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Assistant Dépenses</h1>
                            <p class="mt-2 text-sm text-gray-500">Connectez-vous pour gérer vos reçus et suivre vos dépenses automatiquement.</p>
                        </div>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
