<!-- MODAL EDIT ITEM -->
<div id="modalEditItem"
     class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-md rounded-xl p-6">

        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Edit Item SPK
                </h2>

                <p class="text-sm text-gray-500">
                    Kode item tidak diubah agar tabel penggunaan bahan tetap aman.
                </p>
            </div>

            <button type="button"
                    onclick="closeEditItemModal()"
                    class="text-gray-500 hover:text-black">
                ✕
            </button>
        </div>

        <form id="formEditItem" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-600">Nama Item</label>
                    <input id="edit_nama_item"
                           name="nama_item"
                           required
                           class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- <div>
                    <label class="text-sm text-gray-600">Kategori Item</label>
                    <input id="edit_kategori_item"
                           name="kategori_item"
                           placeholder="Opsional"
                           class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div> -->

                <div>
                    <label class="text-sm text-gray-600">Jumlah Item</label>
                    <input id="edit_jumlah_item"
                           name="jumlah_item"
                           type="number"
                           min="1"
                           required
                           class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button type="button"
                        onclick="closeEditItemModal()"
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