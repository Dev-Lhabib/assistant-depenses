<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Soumettre un reçu</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <form action="{{ route('recus.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="texte_brut" class="block text-sm font-medium text-gray-700 mb-1">
                    Texte du reçu
                </label>
                <textarea
                    id="texte_brut"
                    name="texte_brut"
                    rows="10"
                    placeholder="Colle ici le texte de ton reçu fournisseur..."
                    class="w-full border rounded p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('texte_brut') border-red-500 @enderror"
                >{{ old('texte_brut') }}</textarea>

                @error('texte_brut')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
                    Soumettre
                </button>
                <a href="{{ route('recus.index') }}"
                   class="text-gray-600 px-5 py-2 rounded border hover:bg-gray-50">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</x-app-layout>