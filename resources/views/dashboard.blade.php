<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p>{{ __("You're logged in!") }}</p>
                    <p class="mt-4">Accédez à vos reçus pour créer, afficher et suivre les dépenses extraites.</p>
                    <a href="{{ route('recus.index') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        {{ __('Voir mes reçus') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
