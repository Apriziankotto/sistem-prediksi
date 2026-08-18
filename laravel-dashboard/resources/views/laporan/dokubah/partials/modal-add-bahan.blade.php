<!-- MODAL ADD BAHAN MULTIPLE -->
<div id="modalBahan"
     class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-3xl rounded-xl p-6">

        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Tambah Bahan
                </h2>
                <p class="text-sm text-gray-500">
                    Pilih satu atau beberapa bahan sekaligus.
                </p>
            </div>

            <button type="button"
                    onclick="closeBahanModal()"
                    class="text-gray-500 hover:text-black">
                ✕
            </button>
        </div>

        <input type="text"
               id="searchBahan"
               onkeyup="filterBahan()"
               placeholder="Cari bahan, contoh: B001 atau 3M..."
               class="w-full border rounded-lg p-3 mb-4 focus:outline-none focus:ring-2 focus:ring-sky-500">

        <form method="POST" action="{{ route('permintaan-bahan.add-bahan') }}">
            @csrf

            <input type="hidden" name="spk_id" value="{{ $spk->id }}">

            <div class="max-h-80 overflow-y-auto border rounded-lg">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b text-gray-600 sticky top-0 z-10">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">
                                Pilih
                            </th>
                            <th class="py-3 px-4 text-left">Kode</th>
                            <th class="py-3 px-4 text-left">Nama Bahan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($bahanList as $b)
                            <tr class="border-b bahan-row hover:bg-sky-50 cursor-pointer"
                                onclick="toggleBahanCheckbox(this)">

                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox"
                                           name="bahan_ids[]"
                                           value="{{ $b->id }}"
                                           class="checkbox-bahan"
                                           onclick="event.stopPropagation(); updateSelectedBahan();">
                                </td>

                                <td class="py-3 px-4 font-medium text-sky-600 bahan-kode">
                                    {{ $b->kode_bahan }}
                                </td>

                                <td class="py-3 px-4 bahan-nama">
                                    {{ $b->nama_bahan }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-gray-400">
                                    Tidak ada bahan yang bisa ditambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4">
                <p id="selectedBahanInfo" class="text-sm text-gray-500">
                    Belum ada bahan yang dipilih.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="closeBahanModal()"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                        Batal
                    </button>

                    <button type="submit"
                            id="btnTambahBahan"
                            disabled
                            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                        Tambahkan ke Tabel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>