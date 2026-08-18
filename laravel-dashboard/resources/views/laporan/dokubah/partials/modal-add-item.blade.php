<!-- MODAL ADD ITEM -->
<div id="modalAddItem"
     class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-lg rounded-xl p-6">

        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Tambah Item SPK
                </h2>

                <p class="text-sm text-gray-500">
                    Item otomatis diberi kode A, B, C, dan seterusnya.
                </p>
            </div>

            <button type="button"
                    onclick="closeAddItemModal()"
                    class="text-gray-500 hover:text-black">
                ✕
            </button>
        </div>

        <form method="POST" action="{{ route('item-spk.store') }}">
            @csrf

            <input type="hidden" name="spk_id" value="{{ $spk->id }}">

            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-600">Kode Item</label>
                    <input type="text"
                           value="Otomatis"
                           readonly
                           class="w-full border rounded-lg p-2 bg-gray-100 text-gray-500">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Nama Item</label>
                    <input type="text"
                           name="nama_item"
                           placeholder=""
                           required
                           class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- <div>
                    <label class="text-sm text-gray-600">Kategori Item</label>
                    <input type="text"
                           name="kategori_item"
                           placeholder="Opsional"
                           class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div> -->

                <div>
                    <label class="text-sm text-gray-600">Jumlah Item</label>
                    <input type="number"
                           name="jumlah_item"
                           min="1"
                           placeholder=""
                           required
                           class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button type="button"
                        onclick="closeAddItemModal()"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </button>

                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded">
                    Simpan Item
                </button>
            </div>
        </form>

    </div>
</div>