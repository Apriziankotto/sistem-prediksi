<x-admin-layout>

    <div class="space-y-6">

        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg text-sm">
                <p class="font-semibold mb-1">Data gagal disimpan.</p>

                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <!-- ================= HEADER ================= -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Dokumen Bahan SPK
                </h1>
            </div>
        </div>


        <!-- ================= TABLE CARD ================= -->
        <div class="bg-white p-6 rounded-xl border border-gray-200">

            <!-- ================= CONTROL ATAS ================= -->
            <form method="GET"
                  action="{{ route('permintaan-bahan.index') }}"
                  class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

                <div>
                    <select name="per_page"
                            onchange="this.form.submit()"
                            class="border border-gray-300 rounded-lg px-4 py-2 text-sm h-11 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>

                    </select>
                </div>

                <div class="flex items-center gap-3">

                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search..."
                               class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm w-64 h-11 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    </div>

                    @if(request('search'))
                        <a href="{{ route('permintaan-bahan.index') }}"
                            placeholder="Search..."
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm h-11 inline-flex items-center">
                            Reset
                        </a>
                    @endif

                    <button type="button"
                            onclick="openAddSpkModal()"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-md text-sm h-11">
                        + Tambah
                    </button>

                </div>

            </form>


            <!-- ================= TABLE ================= -->
            <div class="overflow-x-auto border border-gray-200 rounded-xl">

                <table class="w-full text-sm text-left whitespace-nowrap">

                    <thead class="border-b text-gray-600 bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 w-12">No</th>
                            <th class="py-3 px-4">Nomor SPK</th>
                            <th class="py-3 px-4">Nama Project</th>
                            <th class="py-3 px-4">Periode Pengerjaan</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700">

                        @forelse($spk as $s)

                            <tr onclick="window.location='{{ route('permintaan-bahan.show', $s->id) }}'"
                                class="border-b hover:bg-sky-50 cursor-pointer transition duration-150">

                                <td class="py-4 px-4">
                                    {{ ($spk->firstItem() ?? 1) + $loop->index }}
                                </td>

                                <td class="py-4 px-4 font-medium text-sky-600">
                                    {{ $s->nomor_spk }}
                                </td>

                                <td class="py-4 px-4">
                                    {{ $s->nama_proyek }}
                                </td>

                                <td class="py-4 px-4 text-gray-500">
                                    {{ $s->tanggal_mulai ? \Carbon\Carbon::parse($s->tanggal_mulai)->format('d/m/Y') : '-' }}
                                    -
                                    {{ $s->tanggal_selesai ? \Carbon\Carbon::parse($s->tanggal_selesai)->format('d/m/Y') : '-' }}
                                </td>

                                <td class="py-4 px-4">
                                    <div class="flex justify-center gap-2">

                                        <button type="button"
                                                onclick="event.stopPropagation(); openEditSpkModal(
                                                    '{{ $s->id }}',
                                                    @js($s->nomor_spk),
                                                    @js($s->nama_proyek),
                                                    @js($s->tanggal_mulai),
                                                    @js($s->tanggal_selesai)
                                                )"
                                                class="w-8 h-8 flex items-center justify-center rounded-md bg-sky-100 text-sky-600 hover:bg-sky-200 hover:text-sky-700 transition">

                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form action="{{ route('spk.destroy', $s->id) }}"
                                              method="POST"
                                              class="inline"
                                              onclick="event.stopPropagation();">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    onclick="return confirm('Yakin ingin menghapus SPK ini?')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-md bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 transition">

                                                <i class="fa-solid fa-trash"></i>

                                            </button>

                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5"
                                    class="text-center py-8 text-gray-400">
                                    Data SPK tidak tersedia.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-5">
                {{ $spk->links() }}
            </div>

        </div>

    </div>


    @include('laporan.dokubah.partials.modal-add-spk')
    @include('laporan.dokubah.partials.modal-edit-spk')


    <!-- ================= SCRIPT ================= -->
    <script>
        function openAddSpkModal() {
            document.getElementById('modalAddSpk').classList.remove('hidden');
            document.getElementById('modalAddSpk').classList.add('flex');
        }

        function closeAddSpkModal() {
            document.getElementById('modalAddSpk').classList.add('hidden');
            document.getElementById('modalAddSpk').classList.remove('flex');

            const form = document.getElementById('formAddSpk');

            if (form) {
                form.reset();
            }
        }

        function openEditSpkModal(id, nomor, nama, mulai, selesai) {
            const form = document.getElementById('formEditSpk');

            form.action = "{{ url('/spk') }}/" + id;

            document.getElementById('edit_nomor_spk').value = nomor ?? '';
            document.getElementById('edit_nama_proyek').value = nama ?? '';
            document.getElementById('edit_tanggal_mulai').value = mulai ?? '';
            document.getElementById('edit_tanggal_selesai').value = selesai ?? '';

            document.getElementById('modalEditSpk').classList.remove('hidden');
            document.getElementById('modalEditSpk').classList.add('flex');
        }

        function closeEditSpkModal() {
            document.getElementById('modalEditSpk').classList.add('hidden');
            document.getElementById('modalEditSpk').classList.remove('flex');
        }
    </script>

</x-admin-layout>