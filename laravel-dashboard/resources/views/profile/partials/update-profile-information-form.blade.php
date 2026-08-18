<section>
    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-medium text-gray-600 mb-1">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>

            <input id="name"
                   name="name"
                   type="text"
                   value="{{ old('name', $user->name) }}"
                   required
                   autofocus
                   autocomplete="name"
                   placeholder="Masukkan nama lengkap"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500">

            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-xs font-medium text-gray-600 mb-1">
                Alamat Email <span class="text-red-500">*</span>
            </label>

            <input id="email"
                   name="email"
                   type="email"
                   value="{{ old('email', $user->email) }}"
                   required
                   autocomplete="username"
                   placeholder="contoh@email.com"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500">

            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />
        </div>

        @if ($user->role)
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Role / Peran
                </label>

                <input type="text"
                       value="{{ $user->role->nama_role }}"
                       readonly
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-500 focus:outline-none cursor-not-allowed">
            </div>
        @endif

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
                <p class="text-xs text-amber-800">
                    Email Anda belum diverifikasi.

                    <button form="send-verification"
                            class="font-semibold underline hover:text-amber-900 ml-1">
                        Kirim ulang email verifikasi
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-xs font-medium text-emerald-600">
                        Link verifikasi baru telah dikirim ke email Anda.
                    </p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-xs text-emerald-600 font-medium">
                    Data berhasil disimpan.
                </p>
            @endif
        </div>
    </form>
</section>