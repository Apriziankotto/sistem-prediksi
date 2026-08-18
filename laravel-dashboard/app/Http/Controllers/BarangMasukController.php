<?php

namespace App\Http\Controllers;

use App\Models\MasterBahan;
use App\Models\StokBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BarangMasukController extends Controller
{
    /*
        Menampilkan halaman Barang Masuk.

        Data diambil dari tabel stok_bahan.
        Barang masuk dibedakan dengan kolom:
        jenis = masuk
    */
    public function index(Request $request)
    {
        // Ambil input pencarian dari URL
        $search = $request->search;

        // Ambil filter tanggal dari URL
        $tanggalMulai = $request->tanggal_mulai;
        $tanggalSelesai = $request->tanggal_selesai;

        // Ambil jumlah data per halaman
        $perPage = $request->per_page ?? 10;

        // Batasi pilihan jumlah data agar aman
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        /*
            Query utama barang masuk.
            Karena barang masuk disimpan di stok_bahan,
            maka yang diambil hanya data dengan jenis = masuk.
        */
        $barangMasuk = StokBahan::with('masterBahan')
            ->where('jenis', 'masuk')

            // Searching berdasarkan keterangan atau data master bahan
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {

                    // Cari berdasarkan keterangan stok_bahan
                    $q->where('keterangan', 'like', "%{$search}%")

                        // Cari berdasarkan data bahan dari relasi masterBahan
                        ->orWhereHas('masterBahan', function ($bahanQuery) use ($search) {
                            $bahanQuery->where('kode_bahan', 'like', "%{$search}%")
                                ->orWhere('nama_bahan', 'like', "%{$search}%")
                                ->orWhere('kategori_bahan', 'like', "%{$search}%")
                                ->orWhere('satuan', 'like', "%{$search}%");
                        });
                });
            })

            // Filter tanggal mulai berdasarkan created_at
            ->when($tanggalMulai, function ($query) use ($tanggalMulai) {
                $query->whereDate('created_at', '>=', $tanggalMulai);
            })

            // Filter tanggal selesai berdasarkan created_at
            ->when($tanggalSelesai, function ($query) use ($tanggalSelesai) {
                $query->whereDate('created_at', '<=', $tanggalSelesai);
            })

            // Data terbaru tampil paling atas
            ->latest()

            // Pagination sesuai dropdown 10 / 25 / 50 / 100
            ->paginate($perPage)

            // Agar search, filter, dan per_page tidak hilang saat pindah halaman
            ->withQueryString();

        /*
            Ambil data master bahan untuk dropdown tambah/edit.
            Jika kolom status ada, hanya tampilkan bahan aktif.
        */
        $masterBahan = MasterBahan::query()
            ->when(Schema::hasColumn('master_bahan', 'status'), function ($query) {
                $query->where('status', 'aktif');
            })
            ->orderBy('nama_bahan', 'asc')
            ->get();

        /*
            Data ringkasan untuk card atas.
        */
        $totalTransaksi = StokBahan::where('jenis', 'masuk')->count();

        $totalJumlahMasuk = StokBahan::where('jenis', 'masuk')->sum('jumlah');

        $totalHariIni = StokBahan::where('jenis', 'masuk')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return view('laporan.masuk.index', compact(
            'barangMasuk',
            'masterBahan',
            'totalTransaksi',
            'totalJumlahMasuk',
            'totalHariIni'
        ));
    }

    /*
        Menyimpan barang masuk.

        Data disimpan ke tabel stok_bahan:
        master_bahan_id = bahan yang dipilih
        jenis = masuk
        jumlah = jumlah barang masuk
        keterangan = catatan tambahan
    */
    public function store(Request $request)
    {
        $request->validate([
            'master_bahan_id' => 'required|exists:master_bahan,id',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:255',
        ]);

        StokBahan::create([
            'master_bahan_id' => $request->master_bahan_id,
            'jenis' => 'masuk',
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('bahan-masuk.index')
            ->with('success', 'Barang masuk berhasil ditambahkan.');
    }

    /*
        Mengupdate data barang masuk.
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'master_bahan_id' => 'required|exists:master_bahan,id',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:255',
        ]);

        /*
            Ambil data stok_bahan berdasarkan id,
            tetapi hanya yang jenisnya masuk.
            Supaya data barang keluar tidak ikut terubah.
        */
        $barangMasuk = StokBahan::where('jenis', 'masuk')->findOrFail($id);

        $barangMasuk->update([
            'master_bahan_id' => $request->master_bahan_id,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('bahan-masuk.index')
            ->with('success', 'Barang masuk berhasil diperbarui.');
    }

    /*
        Menghapus data barang masuk.

        Jika data barang masuk dihapus,
        maka stok akhir otomatis ikut berkurang karena data masuknya hilang.
    */
    public function destroy($id)
    {
        $barangMasuk = StokBahan::where('jenis', 'masuk')->findOrFail($id);

        $barangMasuk->delete();

        return redirect()
            ->route('bahan-masuk.index')
            ->with('success', 'Barang masuk berhasil dihapus.');
    }
}