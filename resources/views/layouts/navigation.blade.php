<nav x-data="{ open: false }" class="md:flex md:w-72 md:flex-col md:fixed md:inset-y-0 bg-white border-r border-gray-200 shadow-sm">
    <div class="flex h-16 items-center justify-between px-4 border-b border-gray-200 md:h-auto md:flex-col md:items-start md:space-y-6 md:px-6 md:py-6">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-lg font-semibold text-white">AD</div>
            <div class="hidden md:block">
                <h1 class="text-lg font-semibold text-gray-900">Assistant Dépenses</h1>
                <p class="text-sm text-gray-500">Suivi de reçus</p>
            </div>
        </div>

        <button @click="open = !open" class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 md:hidden">
            Menu
        </button>
    </div>

    <div class="hidden md:flex md:flex-col md:grow md:px-6 md:pb-6">
        <div class="mt-4 space-y-2">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-nav-link>
            <x-nav-link :href="route('recus.index')" :active="request()->routeIs('recus.*')">
                {{ __('Reçus') }}
            </x-nav-link>
        </div>

        <div class="mt-auto w-full rounded-3xl bg-slate-50 p-4 text-sm text-gray-700">
            <div class="font-semibold text-gray-900">{{ Auth::user()->name }}</div>
            <div class="mt-1 text-gray-500">{{ Auth::user()->email }}</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full rounded-2xl bg-white border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Se déconnecter</button>
            </form>
        </div>
    </div>

    <div class="md:hidden" :class="open ? 'block' : 'hidden'">
        <div class="space-y-1 px-4 pb-4 pt-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('recus.index')" :active="request()->routeIs('recus.*')">
                {{ __('Reçus') }}
            </x-responsive-nav-link>
        </div>
        <div class="border-t border-gray-200 px-4 py-4">
            <div class="font-semibold text-gray-900">{{ Auth::user()->name }}</div>
            <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Se déconnecter</button>
            </form>
        </div>
    </div>
</nav>