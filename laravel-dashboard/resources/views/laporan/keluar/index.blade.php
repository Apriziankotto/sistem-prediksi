<x-admin-layout>

<style>
    html,
    body {
        max-width: 100vw !important;
        overflow-x: hidden !important;
    }

    #keluarPage {
        width: 100% !important;
        max-width: calc(100vw - 16rem - 3rem) !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
    }

    #keluarPage *,
    #keluarPage *::before,
    #keluarPage *::after {
        box-sizing: border-box;
    }

    .keluar-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
    }

    .keluar-grid {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
    }

    .keluar-grid > * {
        min-width: 0 !important;
        max-width: 100% !important;
    }

    .table-scroll-area {
        display: block;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        -webkit-overflow-scrolling: touch;
    }

    .table-scroll-area::-webkit-scrollbar {
        height: 10px;
    }

    .table-scroll-area::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 9999px;
    }

    .table-scroll-area::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 9999px;
    }

    .table-scroll-area::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    .material-table {
        width: 1320px !important;
        min-width: 1320px !important;
        max-width: none !important;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
    }

    .riwayat-table {
        width: 1450px !important;
        min-width: 1450px !important;
        max-width: none !important;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
    }

    .sticky-bahan-head {
        position: sticky;
        left: 0;
        z-index: 30;
        background: #f9fafb;
    }

    .sticky-bahan-cell {
        position: sticky;
        left: 0;
        z-index: 20;
        background: white;
    }

    tr:hover .sticky-bahan-cell {
        background: #f9fafb;
    }
</style>

<div id="keluarPage" class="space-y-6">

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
            Data gagal disimpan. Periksa kembali inputan Anda.
        </div>
    @endif

    <!-- ================= HEADER HALAMAN ================= -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Bahan Keluar
        </h1>
    </div>

    <div class="keluar-grid grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-receipt"></i>
                </div>

                <div class="min-w-0">
                    <p class="text-sm text-gray-500">Total Transaksi</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $totalTransaksi }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                </div>

                <div class="min-w-0">
                    <p class="text-sm text-gray-500">Total Jumlah Keluar</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ number_format($totalKeluar, 2, ',', '.') }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>

                <div class="min-w-0">
                    <p class="text-sm text-gray-500">Transaksi Hari Ini</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $totalHariIni }}
                    </h3>
                </div>
            </div>
        </div>

    </div>

    <div class="keluar-card bg-white p-6 rounded-xl border border-gray-200">

        <h2 class="text-lg font-bold text-gray-800 mb-1">
            Pilih SPK
        </h2>

        <p class="text-sm text-gray-500 mb-4">
            Pilih SPK untuk melihat item dan bahan yang akan dikeluarkan.
        </p>

        <form method="GET"
              action="{{ route('bahan-keluar.index') }}"
              class="keluar-grid grid grid-cols-1 md:grid-cols-2 gap-3">

            <div class="min-w-0">
                <label class="text-sm text-gray-600">SPK</label>

                <select name="spk_id"
                        onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <option value="">-- Pilih SPK --</option>

                    @foreach($spk as $s)
                        <option value="{{ $s->id }}"
                            {{ request('spk_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nomor_spk ?? 'SPK-'.$s->id }} - {{ $s->nama_proyek ?? '-' }}
                        </option>
                    @endforeach

                </select>
            </div>

        </form>

    </div>

    @if($selectedSpk)

        <div class="keluar-card">

            <h2 class="text-lg font-bold text-gray-800 mb-3">
                Item SPK
            </h2>

            @if($items->count() > 0)

                <div class="keluar-grid grid grid-cols-1 md:grid-cols-2 gap-3">

                    @foreach($items as $item)

                        <a href="{{ route('bahan-keluar.index', ['spk_id' => $selectedSpk->id, 'item_spk_id' => $item->id]) }}"
                           class="block bg-white p-4 border rounded-xl hover:shadow-sm transition min-w-0
                           {{ request('item_spk_id') == $item->id ? 'border-sky-500 bg-sky-50' : 'border-gray-200' }}">

                            <div class="flex items-start gap-3 min-w-0">

                                <div class="w-10 h-10 flex items-center justify-center rounded-full
                                {{ request('item_spk_id') == $item->id ? 'bg-sky-600 text-white' : 'bg-sky-100 text-sky-700' }}
                                font-bold shrink-0">
                                    {{ $item->keterangan ?? '-' }}
                                </div>

                                <div class="min-w-0">
                                    <div class="font-bold text-gray-800 truncate"
                                         title="{{ $item->keterangan }}: {{ $item->nama_item }}">
                                        {{ $item->keterangan }}: {{ $item->nama_item }}
                                    </div>

                                    <div class="text-sm text-gray-500 mt-1">
                                        Jumlah: {{ $item->jumlah_item }}
                                    </div>
                                </div>

                            </div>

                        </a>

                    @endforeach

                </div>

            @else

                <div class="bg-white border border-dashed border-gray-300 rounded-xl p-6 text-center text-gray-400">
                    Belum ada item pada SPK ini.
                </div>

            @endif

        </div>

    @endif

    @if($selectedSpk && $selectedItem)

        <div class="keluar-card bg-white p-6 border border-gray-200 rounded-xl">

            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-800">
                    Bahan Keluar untuk Item {{ $selectedItem->keterangan }}
                </h2>

                <p class="text-sm text-gray-500">
                    Item {{ $selectedItem->nama_item }} jumlahnya {{ $selectedItem->jumlah_item }}.
                    Geser tabel ke kanan untuk melihat kolom lainnya.
                </p>
            </div>

            <div class="table-scroll-area">
                <table class="material-table text-sm text-left whitespace-nowrap">

                    <thead class="border-b text-gray-600 bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 sticky-bahan-head" style="width: 330px;">
                                Bahan
                            </th>

                            <th class="py-3 px-4 text-center" style="width: 120px;">
                                Permintaan
                            </th>

                            <th class="py-3 px-4 text-center" style="width: 130px;">
                                Sudah Keluar
                            </th>

                            <th class="py-3 px-4 text-center" style="width: 130px;">
                                Status
                            </th>

                            <th class="py-3 px-4 text-center" style="width: 130px;">
                                Stok Gudang
                            </th>

                            <th class="py-3 px-4 text-center" style="width: 150px;">
                                Jumlah Keluar
                            </th>

                            <th class="py-3 px-4" style="width: 220px;">
                                Keterangan
                            </th>

                            <th class="py-3 px-4 text-center" style="width: 110px;">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700">

                        @forelse($bahanRows as $row)

                            @php
                                $b = $row['bahan'];
                                $bahanId = $b->id ?? $b->bahan_id ?? $row['bahan_id'];
                                $sisa = $row['sisa'];
                                $stokGudang = $row['stok_gudang'];
                                $bolehKeluar = $stokGudang > 0;
                                $formId = 'formKeluar' . $bahanId;
                            @endphp

                            <tr class="border-b hover:bg-gray-50">

                                <td class="py-4 px-4 whitespace-normal sticky-bahan-cell align-top" style="width: 330px;">
                                    <div class="font-semibold text-gray-800 truncate"
                                         style="max-width: 300px;"
                                         title="{{ $b->kode_bahan }} - {{ $b->nama_bahan }}">
                                        {{ $b->kode_bahan }} -
                                        {{ \Illuminate\Support\Str::limit($b->nama_bahan, 45) }}
                                    </div>

                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ $b->kategori_bahan ?? '-' }} | {{ $b->ukuran ?? '-' }} {{ $b->satuan ?? '-' }}
                                    </div>

                                    <form id="{{ $formId }}"
                                          action="{{ route('bahan-keluar.store') }}"
                                          method="POST"
                                          class="hidden">
                                        @csrf

                                        <input type="hidden" name="spk_id" value="{{ $selectedSpk->id }}">
                                        <input type="hidden" name="item_spk_id" value="{{ $selectedItem->id }}">
                                        <input type="hidden" name="bahan_id" value="{{ $bahanId }}">
                                        <input type="hidden" name="tanggal_aktual" value="{{ date('Y-m-d') }}">
                                    </form>
                                </td>

                                <td class="py-4 px-4 text-center font-semibold align-top">
                                    {{ number_format($row['permintaan'], 2, ',', '.') }}
                                </td>

                                <td class="py-4 px-4 text-center align-top">
                                    {{ number_format($row['sudah_keluar'], 2, ',', '.') }}
                                </td>

                                <td class="py-4 px-4 text-center align-top">
                                    @if($sisa > 0)
                                        <span class="inline-flex px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-medium">
                                            Sisa {{ number_format($sisa, 2, ',', '.') }}
                                        </span>
                                    @elseif($sisa == 0)
                                        <span class="inline-flex px-3 py-1 rounded-full bg-green-100 text-green-600 text-xs font-medium">
                                            Pas
                                        </span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-medium">
                                            Lebih {{ number_format(abs($sisa), 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-center align-top">
                                    @if($stokGudang <= 0)
                                        <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-medium">
                                            Habis
                                        </span>
                                    @else
                                        <span class="font-semibold text-gray-800">
                                            {{ number_format($stokGudang, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-center align-top">
                                    <input type="number"
                                           form="{{ $formId }}"
                                           name="jumlah_aktual"
                                           min="0.01"
                                           step="0.01"
                                           max="{{ $stokGudang }}"
                                           placeholder="0"
                                           {{ !$bolehKeluar ? 'disabled' : '' }}
                                           class="w-28 border border-gray-300 rounded-lg text-center px-2 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                </td>

                                <td class="py-4 px-4 align-top">
                                    <input type="text"
                                           form="{{ $formId }}"
                                           name="keterangan"
                                           placeholder="Opsional"
                                           {{ !$bolehKeluar ? 'disabled' : '' }}
                                           class="w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                </td>

                                <td class="py-4 px-4 text-center align-top">
                                    <button type="submit"
                                            form="{{ $formId }}"
                                            {{ !$bolehKeluar ? 'disabled' : '' }}
                                            class="w-9 h-9 inline-flex items-center justify-center rounded-md bg-sky-600 text-white hover:bg-sky-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                                            title="Simpan bahan keluar">
                                        <i class="fa-solid fa-check text-sm"></i>
                                    </button>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-400">
                                    Belum ada permintaan bahan untuk item ini.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>

    @elseif($selectedSpk)

        <div class="keluar-card bg-white border border-dashed border-gray-300 rounded-xl p-6 text-center text-gray-400">
            Pilih salah satu item SPK untuk melihat bahan yang bisa dikeluarkan.
        </div>

    @endif

    <div class="keluar-card bg-white rounded-xl border border-gray-200 p-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4 min-w-0">
            <div class="min-w-0">
                <h2 class="text-lg font-bold text-gray-800">
                    Riwayat Bahan Keluar
                </h2>

                <p class="text-sm text-gray-500">
                    Daftar realisasi bahan keluar yang sudah tersimpan.
                </p>
            </div>

            <form method="GET"
                  action="{{ route('bahan-keluar.index') }}"
                  class="flex items-center gap-3 min-w-0">

                <input type="hidden" name="spk_id" value="{{ request('spk_id') }}">
                <input type="hidden" name="item_spk_id" value="{{ request('item_spk_id') }}">

                <div class="relative min-w-0">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search..."
                           class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm w-64 max-w-full h-11 focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                </div>

            </form>
        </div>

        <div class="table-scroll-area">
            <table class="riwayat-table text-sm text-left whitespace-nowrap">

                <thead class="border-b text-gray-600 bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-center" style="width: 70px;">No</th>
                        <th class="py-3 px-4" style="width: 120px;">Tanggal</th>
                        <th class="py-3 px-4" style="width: 200px;">SPK</th>
                        <th class="py-3 px-4" style="width: 220px;">Item</th>
                        <th class="py-3 px-4" style="width: 320px;">Bahan</th>
                        <th class="py-3 px-4 text-center" style="width: 140px;">Jumlah Keluar</th>
                        <th class="py-3 px-4" style="width: 260px;">Keterangan</th>
                        <th class="py-3 px-4 text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">

                    @forelse($riwayat as $item)

                        <tr class="border-b hover:bg-gray-50">

                            <td class="py-4 px-4 text-center align-top">
                                {{ ($riwayat->firstItem() ?? 1) + $loop->index }}
                            </td>

                            <td class="py-4 px-4 text-gray-500 align-top">
                                {{ $item->tanggal_aktual ? date('d/m/Y', strtotime($item->tanggal_aktual)) : '-' }}
                            </td>

                            <td class="py-4 px-4 align-top">
                                <div class="font-semibold text-gray-800 max-w-[180px] truncate"
                                     title="{{ $item->spk->nomor_spk ?? '-' }}">
                                    {{ $item->spk->nomor_spk ?? '-' }}
                                </div>

                                <div class="text-xs text-gray-400 max-w-[180px] truncate"
                                     title="{{ $item->spk->nama_proyek ?? '-' }}">
                                    {{ $item->spk->nama_proyek ?? '-' }}
                                </div>
                            </td>

                            <td class="py-4 px-4 align-top">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold text-xs shrink-0">
                                        {{ $item->itemSpk->keterangan ?? '-' }}
                                    </span>

                                    <span class="max-w-[160px] truncate"
                                          title="{{ $item->itemSpk->nama_item ?? '-' }}">
                                        {{ \Illuminate\Support\Str::limit($item->itemSpk->nama_item ?? '-', 28) }}
                                    </span>
                                </div>
                            </td>

                            <td class="py-4 px-4 align-top">
                                <div class="font-semibold text-gray-800 max-w-[300px] truncate"
                                     title="{{ ($item->bahan->kode_bahan ?? '-') . ' - ' . ($item->bahan->nama_bahan ?? '-') }}">
                                    {{ $item->bahan->kode_bahan ?? '-' }} -
                                    {{ \Illuminate\Support\Str::limit($item->bahan->nama_bahan ?? '-', 42) }}
                                </div>

                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $item->bahan->ukuran ?? '-' }} {{ $item->bahan->satuan ?? '-' }}
                                </div>
                            </td>

                            <td class="py-4 px-4 text-center font-semibold align-top">
                                {{ number_format($item->jumlah_aktual, 2, ',', '.') }}
                            </td>

                            <td class="py-4 px-4 align-top">
                                <div class="max-w-[250px] truncate"
                                     title="{{ $item->keterangan ?? '-' }}">
                                    {{ \Illuminate\Support\Str::limit($item->keterangan ?? '-', 50) }}
                                </div>
                            </td>

                            <td class="py-4 px-4 text-center align-top">
                                <div class="flex items-center justify-center gap-2">

                                    <button type="button"
                                            onclick="openEditModal(
                                                @js($item->id),
                                                @js($item->tanggal_aktual),
                                                @js($item->jumlah_aktual),
                                                @js($item->keterangan)
                                            )"
                                            class="w-8 h-8 flex items-center justify-center rounded-md bg-sky-100 text-sky-600 hover:bg-sky-200"
                                            title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </button>

                                    <form action="{{ route('bahan-keluar.destroy', $item->id) }}"
                                          method="POST"
                                          class="inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Hapus data bahan keluar ini? Stok gudang akan ikut berubah.')"
                                                class="w-8 h-8 flex items-center justify-center rounded-md bg-red-100 text-red-600 hover:bg-red-200"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>

                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-400">
                                Belum ada data bahan keluar.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="mt-5">
            {{ $riwayat->links() }}
        </div>

    </div>

    <div id="modalEdit"
         class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

        <div class="bg-white w-full max-w-lg rounded-xl p-6">

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    Edit Bahan Keluar
                </h2>

                <button type="button"
                        onclick="closeEditModal()"
                        class="text-gray-400 hover:text-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="formEdit" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="text-sm text-gray-600">Tanggal Keluar</label>

                    <input id="edit_tanggal_aktual"
                           type="date"
                           name="tanggal_aktual"
                           required
                           class="w-full border rounded-lg p-2 mt-1 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="mb-3">
                    <label class="text-sm text-gray-600">Jumlah Keluar</label>

                    <input id="edit_jumlah_aktual"
                           type="number"
                           name="jumlah_aktual"
                           min="0.01"
                           step="0.01"
                           required
                           class="w-full border rounded-lg p-2 mt-1 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-600">Keterangan</label>

                    <textarea id="edit_keterangan"
                              name="keterangan"
                              rows="3"
                              class="w-full border rounded-lg p-2 mt-1 focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                </div>

                <div class="flex justify-end gap-2">

                    <button type="button"
                            onclick="closeEditModal()"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                        Batal
                    </button>

                    <button type="submit"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded">
                        Update
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
    function openEditModal(id, tanggal, jumlah, keterangan) {
        const modal = document.getElementById('modalEdit');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('edit_tanggal_aktual').value = tanggal ?? '';
        document.getElementById('edit_jumlah_aktual').value = jumlah ?? '';
        document.getElementById('edit_keterangan').value = keterangan ?? '';

        const action = "{{ route('bahan-keluar.update', '__ID__') }}".replace('__ID__', id);

        document.getElementById('formEdit').action = action;
    }

    function closeEditModal() {
        const modal = document.getElementById('modalEdit');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

</x-admin-layout>