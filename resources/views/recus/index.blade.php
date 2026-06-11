<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800"> Mes Reçus </h2>
  </x-slot>

  <div class="py-8 max-w-5xl mx-auto px-4">

    @if(session('success'))
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
      </div>
    @endif

    <div class="mb-4">
      <a href="{{ route('recus.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        + Nouveau reçu
      </a>
    </div>

    @if($recus->isEmpty())
      <p class="text-gray-500">Aucun reçu pour l'instant.</p>
    @else
      <table class="w-full border-collapse bg-white shadow rounded">
        <thead class="bg-gray-100 text-left text-sm text-gray-600">
          <tr>
            <th class="p-3">Extrait</th>
            <th class="p-3">Statut</th>
            <th class="p-3">Dépenses</th>
            <th class="p-3">Date</th>
            <th class="p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($recus as $recu)
          <tr class="border-t text-sm">
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs font-medium
                {{ $recu->statut === 'traite' ? 'bg-green-100 text-green-700' : '' }}
                {{ $recu->statut === 'en_attente' ? 'bg-orange-100 text-orange-700' : '' }}
                {{ $recu->statut === 'echoue' ? 'bg-red-100 text-red-700' : '' }}
              ">
                {{ $recu->statut === 'en_attente' ? 'En attente' : ($recu->statut === 'traite' ? 'Traité' : 'Échoué') }}
              </span>
            </td>
            <td class="p-3">{{ $recu->depenses_count }}</td>
            <td class="p-3">{{ $recu->created_at->format('d/m/Y') }}</td>
            <td class="p-3 flex gap-2">
              <a href="{{ route('recus.show', $recu) }}"
                class="text-blue-600 hover:underline">Voir</a>
              <form action="{{ route('recus.destroy' , $recu) }}" method="POST" onsubmit="return confirm('Supprimer ce reçu ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">
                  Supprimer
                </button>
              </form>  
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</x-app-layout>