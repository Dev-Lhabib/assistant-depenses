<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Tableau de bord</h2>
                <p class="mt-1 text-sm text-gray-600">Suivez vos reçus et dépenses extraites en un seul endroit.</p>
            </div>
            <a href="{{ route('recus.create') }}" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">Ajouter un reçu</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-green-900 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-4 lg:grid-cols-2">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm uppercase tracking-[0.2em] text-gray-500">Reçus</p>
                    <p class="mt-5 text-4xl font-semibold text-gray-900">{{ $totalRecus }}</p>
                    <p class="mt-2 text-sm text-gray-500">Reçus créés</p>
                </div>
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm uppercase tracking-[0.2em] text-gray-500">Dépenses</p>
                    <p class="mt-5 text-4xl font-semibold text-gray-900">{{ $totalDepenses }}</p>
                    <p class="mt-2 text-sm text-gray-500">Lignes extraites</p>
                </div>
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm uppercase tracking-[0.2em] text-gray-500">En attente</p>
                    <p class="mt-5 text-4xl font-semibold text-gray-900">{{ $pendingRecus }}</p>
                    <p class="mt-2 text-sm text-gray-500">Reçus en cours</p>
                </div>
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm uppercase tracking-[0.2em] text-gray-500">Traités</p>
                    <p class="mt-5 text-4xl font-semibold text-gray-900">{{ $processedRecus }}</p>
                    <p class="mt-2 text-sm text-gray-500">Reçus analysés</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-3 lg:grid-cols-2">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Vue d’activité</h3>
                            <p class="mt-2 text-sm text-gray-500">Suivez le traitement et l’état de vos derniers reçus.</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Automatique</span>
                    </div>
                    <div class="mt-6 grid gap-4">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Reçus traités</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $processedRecus }}</p>
                        </div>
                        <div class="rounded-2xl bg-amber-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-amber-600">Reçus en attente</p>
                            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ $pendingRecus }}</p>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-2 rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Reçus récents</h3>
                            <p class="mt-2 text-sm text-gray-500">Derniers reçus analysés et en attente.</p>
                        </div>
                        <a href="{{ route('recus.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Voir tout</a>
                    </div>

                    @if($recentRecus->isEmpty())
                        <p class="mt-6 text-sm text-gray-500">Aucun reçu encore. Commencez en ajoutant un reçu.</p>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach($recentRecus as $recu)
                                <a href="{{ route('recus.show', $recu) }}" class="block rounded-3xl border border-gray-200 bg-slate-50 p-5 hover:border-blue-300 hover:bg-white transition">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="space-y-2">
                                            <h4 class="text-base font-semibold text-gray-900">{{ Str::limit($recu->texte_brut, 60) }}</h4>
                                            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                                <span>{{ $recu->created_at->format('d/m/Y') }}</span>
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1">{{ $recu->depenses_count }} dépense(s)</span>
                                            </div>
                                        </div>
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $recu->statut->label() }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
