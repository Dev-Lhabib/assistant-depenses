<x-guest-layout>
    <div class="space-y-6">
        <x-auth-session-status class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-900" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
                <input id="email" name="email" type="email" autocomplete="username" required class="mt-1 block w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" value="{{ old('email') }}" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-1 block w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between text-sm text-gray-600">
                <label class="inline-flex items-center gap-2">
                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    Se souvenir de moi
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="font-semibold text-blue-600 hover:text-blue-800">Mot de passe oublié ?</a>
                @endif
            </div>

            <button type="submit" class="w-full rounded-3xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-blue-700">Se connecter</button>
        </form>

        <p class="text-center text-sm text-gray-500">Pas encore de compte ?
            <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-800">Créer un compte</a>
        </p>
    </div>
</x-guest-layout>
