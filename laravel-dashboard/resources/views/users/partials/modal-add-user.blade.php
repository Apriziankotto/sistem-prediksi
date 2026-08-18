<div id="modalAddUser"
     class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-xl rounded-xl p-6">

        <div class="flex items-start justify-between mb-5">

            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Tambah User
                </h2>

                <p class="text-sm text-gray-500">
                    Tambahkan akun baru dan tentukan role pengguna.
                </p>
            </div>

            <button type="button"
                    onclick="closeAddUserModal()"
                    class="text-gray-400 hover:text-gray-700">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="space-y-4">

                <div>
                    <label class="text-sm text-gray-600">Nama User</label>

                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="Contoh: Aprizian"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Email</label>

                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           placeholder="contoh@email.com"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Role</label>

                    <select name="role_id"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">

                        <option value="">-- Pilih Role --</option>

                        @foreach($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->nama_role }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                    <div>
                        <label class="text-sm text-gray-600">Password</label>

                        <input type="password"
                               name="password"
                               required
                               placeholder="Minimal 6 karakter"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Konfirmasi Password</label>

                        <input type="password"
                               name="password_confirmation"
                               required
                               placeholder="Ulangi password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                </div>

            </div>

            <div class="flex justify-end gap-2 mt-6">

                <button type="button"
                        onclick="closeAddUserModal()"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </button>

                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded">
                    Simpan User
                </button>

            </div>

        </form>

    </div>

</div>

<script>
    function openAddUserModal() {
        const modal = document.getElementById('modalAddUser');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAddUserModal() {
        const modal = document.getElementById('modalAddUser');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>