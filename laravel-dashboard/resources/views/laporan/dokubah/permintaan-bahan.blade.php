<x-admin-layout>

<style>
    html,
    body {
        max-width: 100vw !important;
        overflow-x: hidden !important;
    }

    #dokubahPage {
        width: 100% !important;
        max-width: calc(100vw - 16rem - 3rem) !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
    }

    #dokubahPage *,
    #dokubahPage *::before,
    #dokubahPage *::after {
        box-sizing: border-box;
    }

    .dokubah-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
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
        width: 1600px !important;
        min-width: 1600px !important;
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

<div id="dokubahPage" class="space-y-6">

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

    <!-- BACK -->
    <a href="{{ route('permintaan-bahan.index') }}"
       class="inline-flex items-center gap-2 text-gray-500 hover:text-sky-600 mb-5">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Kembali</span>
    </a>

    <!-- HEADER -->
    <div class="dokubah-card bg-white rounded-xl border p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            Detail Dokubah / SPK
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-400">Nomor SPK</p>
                <p class="font-semibold text-gray-800">
                    {{ $spk->nomor_spk }}
                </p>
            </div>

            <div>
                <p class="text-gray-400">Nama Project</p>
                <p class="font-semibold text-gray-800">
                    {{ $spk->nama_proyek }}
                </p>
            </div>

            <div>
                <p class="text-gray-400">Periode</p>
                <p class="font-semibold text-gray-800">
                    {{ $spk->tanggal_mulai ? \Carbon\Carbon::parse($spk->tanggal_mulai)->format('d/m/Y') : '-' }} - 
                    {{ $spk->tanggal_selesai ? \Carbon\Carbon::parse($spk->tanggal_selesai)->format('d/m/Y') : '-' }}
                </p>
            </div>
        </div>
    </div>

    <!-- DAFTAR ITEM -->
    <div class="dokubah-card bg-white p-6 border border-gray-200 rounded-xl">

        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-1">
                    Daftar Item SPK
                </h2>

                <p class="text-sm text-gray-500">
                    Kode item digunakan sebagai kolom pada tabel penggunaan bahan.
                </p>
            </div>

            <button type="button"
                    onclick="openAddItemModal()"
                    class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-md text-sm">
                + Tambah Item
            </button>
        </div>

        <div class="w-full max-w-full overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="border-b text-gray-600 bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4 w-24">Kode</th>
                        <th class="py-3 px-4">Nama Item</th>
                        <th class="py-3 px-4 text-center">Jumlah</th>
                        <th class="py-3 px-4 text-center w-32">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                    @forelse($items as $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-4 px-4">
                                {{ $loop->iteration }}
                            </td>

                            <td class="py-4 px-4 font-bold text-sky-700">
                                {{ $item->keterangan }}
                            </td>

                            <td class="py-4 px-4 font-medium">
                                {{ $item->nama_item }}
                            </td>

                            <td class="py-4 px-4 text-center">
                                {{ $item->jumlah_item }}
                            </td>

                            <td class="py-4 px-4">
                                <div class="flex justify-center gap-2">
                                    <button type="button"
                                            onclick="openEditItemModal('{{ $item->id }}', @js($item->nama_item), @js($item->kategori_item ?? ''), '{{ $item->jumlah_item }}')"
                                            class="w-8 h-8 flex items-center justify-center rounded-md bg-sky-100 text-sky-600 hover:bg-sky-200">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </button>

                                    <form action="{{ route('item-spk.destroy', $item->id) }}"
                                          method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Hapus item ini? Data penggunaan bahan pada item ini juga akan terhapus.')"
                                                class="w-8 h-8 flex items-center justify-center rounded-md bg-red-100 text-red-600 hover:bg-red-200">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">
                                Belum ada item SPK.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PILIH BAHAN -->
    <div class="dokubah-card bg-white p-6 border border-gray-200 rounded-xl">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">
                    Pilih Bahan yang Digunakan
                </h2>

                <p class="text-sm text-gray-500">
                    Tambahkan bahan yang diperlukan ke dalam tabel.
                </p>
            </div>

            <button type="button"
                    onclick="openBahanModal()"
                    class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $items->count() == 0 || $bahanList->count() == 0 ? 'disabled' : '' }}>
                + Tambah Bahan
            </button>
        </div>

        @if($items->count() == 0)
            <p class="text-xs text-red-500 mt-2">
                Tambahkan item SPK terlebih dahulu sebelum memilih bahan.
            </p>
        @elseif($bahanList->count() == 0)
            <p class="text-xs text-gray-400 mt-2">
                Semua bahan sudah masuk ke tabel penggunaan.
            </p>
        @endif
    </div>

    <!-- TABEL PENGGUNAAN BAHAN -->
        <div class="dokubah-card bg-white p-6 border border-gray-200 rounded-xl">

            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-800">
                    Tabel Penggunaan Bahan
                </h2>
            </div>

            <div class="table-scroll-area">
                <table class="material-table text-sm text-left whitespace-nowrap">

                    <thead class="border-b text-gray-600 bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 sticky-bahan-head"
                                style="width: 330px;">
                                Bahan
                            </th>

                            @foreach($items as $item)
                                <th class="py-3 px-4 text-center"
                                    style="width: 150px;"
                                    title="{{ $item->keterangan }}: {{ $item->nama_item }} jumlahnya {{ $item->jumlah_item }}">
                                    {{ $item->keterangan }}
                                </th>
                            @endforeach

                            <th class="py-3 px-4 text-center bg-gray-100"
                                style="width: 120px;">
                                Total
                            </th>

                            <th class="py-3 px-4 text-center"
                                style="width: 100px;">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700">

                        @forelse($bahanDipakai as $b)
                            <tr class="border-b hover:bg-gray-50">

                                <td class="py-4 px-4 whitespace-normal sticky-bahan-cell align-top"
                                    style="width: 330px;">
                                    <div class="font-semibold text-gray-800 truncate"
                                        style="max-width: 300px;"
                                        title="{{ $b->kode_bahan }} - {{ $b->nama_bahan }}">
                                        {{ $b->kode_bahan }} -
                                        {{ \Illuminate\Support\Str::limit($b->nama_bahan, 45) }}
                                    </div>

                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ $b->kategori_bahan ?? '-' }} | {{ $b->ukuran ?? '-' }} {{ $b->satuan ?? '-' }}
                                    </div>
                                </td>

                                @php $total = 0; @endphp

                                @foreach($items as $item)
                                    @php
                                        $val = $matrix[$b->id][$item->id] ?? 0;
                                        $total += $val;
                                    @endphp

                                    <td class="py-4 px-4 text-center align-top"
                                        style="width: 150px;">
                                        <input type="number"
                                            min="0"
                                            step="0.01"
                                            value="{{ $val }}"
                                            data-bahan-id="{{ $b->id }}"
                                            data-item-id="{{ $item->id }}"
                                            class="penggunaan-input w-28 border border-gray-300 rounded-lg text-center px-2 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                            oninput="handleInput({{ $spk->id }}, {{ $item->id }}, {{ $b->id }}, this)"
                                            onblur="saveData({{ $spk->id }}, {{ $item->id }}, {{ $b->id }}, this.value)">
                                    </td>
                                @endforeach

                                <td class="py-4 px-4 text-center font-bold bg-gray-50 align-top"
                                    style="width: 120px;"
                                    id="total-bahan-{{ $b->id }}">
                                    {{ $total }}
                                </td>

                                <td class="py-4 px-4 text-center align-top"
                                    style="width: 100px;">
                                    <form action="{{ route('permintaan-bahan.hapus-bahan', [$spk->id, $b->id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Hapus bahan ini dari tabel penggunaan?')"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-red-100 text-red-600 hover:bg-red-200">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $items->count() + 3 }}"
                                    class="text-center py-8 text-gray-400">
                                    Belum ada bahan yang dipilih.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>

    </div>

@include('laporan.dokubah.partials.modal-add-item')
@include('laporan.dokubah.partials.modal-add-bahan')
@include('laporan.dokubah.partials.modal-edit-item')

<script>
    function openAddItemModal() {
        document.getElementById('modalAddItem').classList.remove('hidden');
        document.getElementById('modalAddItem').classList.add('flex');
    }

    function closeAddItemModal() {
        document.getElementById('modalAddItem').classList.add('hidden');
        document.getElementById('modalAddItem').classList.remove('flex');
    }

    function openEditItemModal(id, nama, kategori, jumlah) {
        document.getElementById('formEditItem').action = "{{ url('/item-spk') }}/" + id;
        document.getElementById('edit_nama_item').value = nama;
        document.getElementById('edit_jumlah_item').value = jumlah;

        const kategoriInput = document.getElementById('edit_kategori_item');
        if (kategoriInput) {
            kategoriInput.value = kategori;
        }

        document.getElementById('modalEditItem').classList.remove('hidden');
        document.getElementById('modalEditItem').classList.add('flex');
    }

    function closeEditItemModal() {
        document.getElementById('modalEditItem').classList.add('hidden');
        document.getElementById('modalEditItem').classList.remove('flex');
    }

    function openBahanModal() {
        document.getElementById('modalBahan').classList.remove('hidden');
        document.getElementById('modalBahan').classList.add('flex');
    }

    function closeBahanModal() {
        document.getElementById('modalBahan').classList.add('hidden');
        document.getElementById('modalBahan').classList.remove('flex');

        const searchInput = document.getElementById('searchBahan');
        if (searchInput) {
            searchInput.value = '';
        }

        document.querySelectorAll('.bahan-row').forEach(row => {
            row.style.display = '';
            row.classList.remove('bg-sky-100');
        });

        document.querySelectorAll('.checkbox-bahan').forEach(checkbox => {
            checkbox.checked = false;
        });

        updateSelectedBahan();
    }

    function filterBahan() {
        let input = document.getElementById('searchBahan').value.toLowerCase();
        let rows = document.querySelectorAll('.bahan-row');

        rows.forEach(row => {
            let kode = row.querySelector('.bahan-kode').innerText.toLowerCase();
            let nama = row.querySelector('.bahan-nama').innerText.toLowerCase();

            row.style.display = kode.includes(input) || nama.includes(input) ? '' : 'none';
        });
    }

    function toggleBahanCheckbox(row) {
        const checkbox = row.querySelector('.checkbox-bahan');

        if (!checkbox) {
            return;
        }

        checkbox.checked = !checkbox.checked;
        updateSelectedBahan();
    }

    function updateSelectedBahan() {
        const checkedBoxes = document.querySelectorAll('.checkbox-bahan:checked');
        const btnTambahBahan = document.getElementById('btnTambahBahan');
        const selectedInfo = document.getElementById('selectedBahanInfo');

        document.querySelectorAll('.bahan-row').forEach(row => {
            const checkbox = row.querySelector('.checkbox-bahan');

            if (checkbox && checkbox.checked) {
                row.classList.add('bg-sky-100');
            } else {
                row.classList.remove('bg-sky-100');
            }
        });

        if (btnTambahBahan) {
            btnTambahBahan.disabled = checkedBoxes.length === 0;
        }

        if (selectedInfo) {
            selectedInfo.innerText = checkedBoxes.length === 0
                ? 'Belum ada bahan yang dipilih.'
                : checkedBoxes.length + ' bahan dipilih.';
        }
    }

    let saveTimers = {};

    function formatNumber(value) {
        return Number(value)
            .toFixed(2)
            .replace(/\.00$/, '')
            .replace(/(\.\d)0$/, '$1');
    }

    function updateTotal(bahanId) {
        let total = 0;

        document.querySelectorAll(`input[data-bahan-id="${bahanId}"]`).forEach(function(input) {
            let value = parseFloat(input.value);

            if (!isNaN(value)) {
                total += value;
            }
        });

        let totalElement = document.getElementById(`total-bahan-${bahanId}`);

        if (totalElement) {
            totalElement.innerText = formatNumber(total);
        }
    }

    function handleInput(spkId, itemSpkId, bahanId, inputElement) {
        updateTotal(bahanId);

        const key = spkId + '-' + itemSpkId + '-' + bahanId;

        clearTimeout(saveTimers[key]);

        saveTimers[key] = setTimeout(function() {
            saveData(spkId, itemSpkId, bahanId, inputElement.value);
        }, 700);
    }

    function saveData(spk_id, item_spk_id, bahan_id, value) {
        updateTotal(bahan_id);

        fetch("{{ route('permintaan-bahan.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                spk_id: spk_id,
                item_spk_id: item_spk_id,
                bahan_id: bahan_id,
                jumlah_permintaan: value
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log("Tersimpan", data);
        })
        .catch(error => {
            console.error("Gagal menyimpan", error);
        });
    }
</script>

</x-admin-layout>