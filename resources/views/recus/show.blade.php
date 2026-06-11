<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Détail du reçu</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">

        {{-- Texte source --}}
        <div class="bg-white shadow rounded p-5">
            <h3 class="font-medium text-gray-700 mb-2">Texte source</h3>
            <pre class="text-sm text-gray-600 whitespace-pre-wrap">{{ $recu->texte_source }}</pre>
        </div>

        {{-- Statut --}}
        <div class="bg-white shadow rounded p-5">
            <h3 class="font-medium text-gray-700 mb-2">Statut</h3>
            <span class="px-3 py-1 rounded text-sm font-medium
                {{ $recu->statut === 'traite' ? 'bg-green-100 text-green-700' : '' }}
                {{ $recu->statut === 'en_attente' ? 'bg-orange-100 text-orange-700' : '' }}
                {{ $recu->statut === 'echoue' ? 'bg-red-100 text-red-700' : '' }}
            ">
                {{ $recu->statut === 'en_attente' ? 'En attente' : ($recu->statut === 'traite' ? 'Traité' : 'Échoué') }}
            </span>
        </div>

        {{-- Dépenses --}}
        <div class="bg-white shadow rounded p-5">
            <h3 class="font-medium text-gray-700 mb-3">Dépenses extraites</h3>

            @if($recu->depenses->isEmpty())
                <p class="text-sm text-gray-400">Aucune dépense extraite pour l'instant.</p>
            @else
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-100 text-left text-gray-600">
                        <tr>
                            <th class="p-2">Libellé</th>
                            <th class="p-2">Quantité</th>
                            <th class="p-2">Prix unitaire</th>
                            <th class="p-2">Catégorie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recu->depenses as $depense)
                        <tr class="border-t">
                            <td class="p-2">{{ $depense->libelle }}</td>
                            <td class="p-2">{{ $depense->quantite }}</td>
                            <td class="p-2">{{ number_format($depense->prix_unitaire, 2) }} MAD</td>
                            <td class="p-2">{{ $depense->categorie }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <a href="{{ route('recus.index') }}" class="text-blue-600 hover:underline text-sm">
            ← Retour à la liste
        </a>
    </div>
</x-app-layout>