<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dépenses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Filter Section -->
                    <div class="mb-6">
                        <form method="GET" action="{{ route('depenses.index') }}" class="flex gap-4 items-end">
                            <div>
                                <label for="categorie" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Filtrer par catégorie') }}
                                </label>
                                <select 
                                    id="categorie" 
                                    name="categorie" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="">{{ __('Toutes les catégories') }}</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->value }}" @selected(request('categorie') === $cat->value)>
                                            {{ $cat->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button 
                                type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                            >
                                {{ __('Filtrer') }}
                            </button>
                            @if (request('categorie'))
                                <a 
                                    href="{{ route('depenses.index') }}" 
                                    class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded"
                                >
                                    {{ __('Réinitialiser') }}
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Expenses Table -->
                    @if ($depenses->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-left">
                                            {{ __('Libellé') }}
                                        </th>
                                        <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-right">
                                            {{ __('Quantité') }}
                                        </th>
                                        <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-right">
                                            {{ __('Prix unitaire') }}
                                        </th>
                                        <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-right">
                                            {{ __('Total') }}
                                        </th>
                                        <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-left">
                                            {{ __('Catégorie') }}
                                        </th>
                                        <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-left">
                                            {{ __('Reçu') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($depenses as $depense)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2">
                                                {{ $depense->libelle }}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-right">
                                                {{ $depense->quantite }}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-right">
                                                {{ number_format($depense->prix_unitaire, 2) }} MAD
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-right">
                                                {{ number_format($depense->quantite * $depense->prix_unitaire, 2) }} MAD
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2">
                                                <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded text-sm">
                                                    {{ $depense->categorie->label() }}
                                                </span>
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2">
                                                <a 
                                                    href="{{ route('recus.show', $depense->recu_id) }}" 
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200 underline"
                                                >
                                                    {{ __('Voir') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-700 rounded">
                            <p class="text-lg font-semibold">
                                {{ __('Total : ') }}
                                <span class="text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($depenses->sum(fn ($d) => $d->quantite * $d->prix_unitaire), 2) }} MAD
                                </span>
                            </p>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">
                                {{ __('Aucune dépense trouvée.') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
