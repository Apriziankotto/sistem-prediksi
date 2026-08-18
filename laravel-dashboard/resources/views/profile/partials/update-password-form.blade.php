<section>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password"
                   class="block text-xs font-medium text-gray-600 mb-1">
                Password Saat Ini <span class="text-red-500">*</span>
            </label>

            <input id="update_password_current_password"
                   name="current_password"
                   type="password"
                   autocomplete="current-password"
                   placeholder="Masukkan password saat ini"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500">

            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password"
                   class="block text-xs font-medium text-gray-600 mb-1">
                Password Baru <span class="text-red-500">*</span>
            </label>

            <input id="update_password_password"
                   name="password"
                   type="password"
                   autocomplete="new-password"
                   placeholder="Masukkan password baru"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500">

            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password_confirmation"
                   class="block text-xs font-medium text-gray-600 mb-1">
                Konfirmasi Password Baru <span class="text-red-500">*</span>
            </label>

            <input id="update_password_password_confirmation"
                   name="password_confirmation"
                   type="password"
                   autocomplete="new-password"
                   placeholder="Ulangi password baru"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500">

            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Simpan Password
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-xs text-emerald-600 font-medium">
                    Password berhasil diperbarui.
                </p>
            @endif
        </div>
    </form>
</section>