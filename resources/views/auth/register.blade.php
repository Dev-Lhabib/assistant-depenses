<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-3 text-center">
            <h2 class="text-2xl font-semibold text-gray-900">Créer un compte</h2>
            <p class="text-sm text-gray-500">Commencez à gérer vos reçus et dépenses directement depuis votre tableau de bord.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom complet</label>
                <input id="name" name="name" type="text" autocomplete="name" required class="mt-1 block w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" value="{{ old('name') }}" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
                <input id="email" name="email" type="email" autocomplete="username" required class="mt-1 block w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" value="{{ old('email') }}" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required class="mt-1 block w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="mt-1 block w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit" class="w-full rounded-3xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-blue-700">Créer un compte</button>
        </form>

        <p class="text-center text-sm text-gray-500">Vous avez déjà un compte ?
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-800">Se connecter</a>
        </p>
    </div>
</x-guest-layout>
