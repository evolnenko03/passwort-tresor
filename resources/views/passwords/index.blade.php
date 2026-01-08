<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }} | Passwortgenerator</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-black via-slate-950 to-slate-900 text-slate-100">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center justify-center px-6 py-12">
            <section
                class="w-full max-w-lg rounded-2xl border border-slate-800/80 bg-slate-950/80 p-6 shadow-2xl shadow-black/60 backdrop-blur"
                x-data="passwordGenerator"
            >
                <header class="flex flex-col gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-400">Sichere Passwörter</p>
                    <h1 class="text-2xl font-semibold text-white">Passwortgenerator</h1>
                    <p class="text-sm text-slate-300">
                        Erstellen Sie sichere Passwörter direkt im Browser.
                    </p>
                </header>

                <div class="mt-6 flex flex-col gap-6">
                    @include('passwords.components.length-selector')
                    @include('passwords.components.character-types')
                    @include('passwords.components.minimum-counts')

                    <div class="flex flex-col gap-3">
                        @include('passwords.components.action-buttons')
                        @include('passwords.components.messages')
                    </div>

                    @include('passwords.components.password-input')
                    @include('passwords.components.strength-indicator')
                </div>
            </section>
        </main>
    </body>
</html>
