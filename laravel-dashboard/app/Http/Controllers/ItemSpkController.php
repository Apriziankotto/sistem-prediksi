<?php

namespace App\Http\Controllers;

use App\Models\ItemSpk;
use App\Models\DetailBahanPermintaan;
use Illuminate\Http\Request;
use App\Models\DetailBahanAktual;
use App\Models\StokBahan;

class ItemSpkController extends Controller
{
    /*
        Menyimpan item SPK baru.

        Aturan:
        - Dalam 1 SPK, item otomatis diberi kode A, B, C, dst.
        - Kode A/B/C disimpan ke kolom keterangan.
        - Huruf tidak boleh sama dalam 1 SPK.
    */
    public function store(Request $request)
    {
        $request->validate([
            'spk_id' => 'required|exists:spk,id',
            'nama_item' => 'required|string|max:255',
            'kategori_item' => 'nullable|string|max:255',
            'jumlah_item' => 'required|numeric|min:1',
        ]);

        $hurufItem = $this->getNextItemLetter($request->spk_id);

        $item = ItemSpk::create([
            'spk_id' => $request->spk_id,
            'nama_item' => $request->nama_item,
            'kategori_item' => $request->kategori_item,
            'jumlah_item' => $request->jumlah_item,
            'keterangan' => $hurufItem,
        ]);

        /*
            Jika sudah ada bahan yang dipilih pada SPK ini,
            item baru otomatis dibuatkan detail bahan dengan nilai awal 0.
        */
        $bahanIds = DetailBahanPermintaan::where('spk_id', $request->spk_id)
            ->select('bahan_id')
            ->distinct()
            ->pluck('bahan_id');

        foreach ($bahanIds as $bahanId) {
            DetailBahanPermintaan::updateOrCreate(
                [
                    'spk_id' => $request->spk_id,
                    'item_spk_id' => $item->id,
                    'bahan_id' => $bahanId,
                ],
                [
                    'jumlah_permintaan' => 0,
                    'tanggal_permintaan' => now(),
                ]
            );
        }

        return back()->with('success', 'Item ' . $hurufItem . ' berhasil ditambahkan.');
    }

    /*
        Mengupdate data item SPK.

        Catatan:
        - Kode item A/B/C yang tersimpan di keterangan tidak diubah.
        - Yang diubah hanya nama_item, kategori_item, dan jumlah_item.
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_item' => 'required|string|max:255',
            'kategori_item' => 'nullable|string|max:255',
            'jumlah_item' => 'required|numeric|min:1',
        ]);

        $item = ItemSpk::findOrFail($id);

        $item->update([
            'nama_item' => $request->nama_item,
            'kategori_item' => $request->kategori_item,
            'jumlah_item' => $request->jumlah_item,
        ]);

        return back()->with('success', 'Item ' . $item->keterangan . ' berhasil diperbarui.');
    }

    /*
        Menghapus item SPK.

        Saat item dihapus:
        - Data item dihapus.
        - Data penggunaan bahan yang terhubung dengan item tersebut juga dihapus.
    */
    public function destroy($id)
    {
        $item = ItemSpk::findOrFail($id);

        /*
            Ambil semua bahan keluar/realisasi yang terkait dengan item ini.
            Data ini perlu dihapus agar tidak menjadi yatim setelah item dihapus.
        */
        $aktualRows = DetailBahanAktual::where('item_spk_id', $item->id)->get();

        foreach ($aktualRows as $aktual) {
            /*
                Hapus pergerakan stok keluar yang terkait dengan data aktual.
                Sistem kamu menandai stok_bahan dengan AKTUAL_ID di kolom keterangan.
            */
            StokBahan::where('jenis', 'keluar')
                ->where('keterangan', 'like', '%AKTUAL_ID:' . $aktual->id . '%')
                ->delete();

            /*
                Hapus data bahan keluar.
            */
            $aktual->delete();
        }

        /*
            Hapus detail permintaan bahan untuk item ini.
        */
        DetailBahanPermintaan::where('item_spk_id', $item->id)->delete();

        /*
            Hapus item SPK.
        */
        $item->delete();

        return back()->with('success', 'Item berhasil dihapus. Data bahan keluar dan stok terkait juga sudah disesuaikan.');
    }

    /*
        Menentukan huruf berikutnya untuk item dalam 1 SPK.
        Contoh:
        item pertama = A
        item kedua = B
        item ketiga = C
    */
    private function getNextItemLetter($spkId)
    {
        $usedLetters = ItemSpk::where('spk_id', $spkId)
            ->pluck('keterangan')
            ->map(function ($value) {
                return strtoupper(trim($value));
            })
            ->toArray();

        /*
            Mendukung A-Z lalu AA, AB, AC, dst.
        */
        for ($i = 1; $i <= 702; $i++) {
            $letter = $this->numberToLetter($i);

            if (!in_array($letter, $usedLetters)) {
                return $letter;
            }
        }

        throw new \Exception('Kode item sudah penuh.');
    }

    /*
        Mengubah angka menjadi huruf seperti Excel:
        1 = A
        2 = B
        26 = Z
        27 = AA
    */
    private function numberToLetter($number)
    {
        $letter = '';

        while ($number > 0) {
            $mod = ($number - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $number = intdiv($number - $mod, 26);
        }

        return $letter;
    }
}