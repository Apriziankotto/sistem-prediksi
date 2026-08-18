<?php

namespace App\Http\Controllers;

use App\Models\Spk;
use App\Models\MasterBahan;
use App\Models\DetailBahanPermintaan;
use Illuminate\Http\Request;
use App\Models\DetailBahanAktual;
use App\Models\StokBahan;

class DetailBahanPermintaanController extends Controller
{
    /*
        Halaman awal Dokubah / Permintaan Bahan.
        Menampilkan daftar SPK.
    */
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = $request->per_page ?? 10;

        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $spk = Spk::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_spk', 'like', "%{$search}%")
                        ->orWhere('nama_proyek', 'like', "%{$search}%")
                        ->orWhere('tanggal_mulai', 'like', "%{$search}%")
                        ->orWhere('tanggal_selesai', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('laporan.dokubah.index', compact('spk'));
    }

    /*
        Halaman detail setelah klik salah satu SPK.
    */
    public function show($spk_id)
    {
        $spk = Spk::with(['items' => function ($query) {
            $query->orderBy('keterangan', 'asc');
        }])->findOrFail($spk_id);

        $items = $spk->items;

        /*
            Karena detail_bahan_permintaan.bahan_id sekarang berisi kode_bahan,
            maka yang diambil adalah kode bahan, bukan id angka.
        */
        $kodeBahanDipakai = DetailBahanPermintaan::where('spk_id', $spk->id)
            ->select('bahan_id')
            ->distinct()
            ->pluck('bahan_id')
            ->filter()
            ->values();

        /*
            Bahan yang sudah dipilih.
            Dicari berdasarkan master_bahan.kode_bahan.
        */
        $bahanDipakai = MasterBahan::whereIn('kode_bahan', $kodeBahanDipakai)
            ->orderBy('nama_bahan', 'asc')
            ->get();

        /*
            Bahan yang belum dipilih.
        */
        $bahanList = MasterBahan::whereNotIn('kode_bahan', $kodeBahanDipakai)
            ->orderBy('nama_bahan', 'asc')
            ->get();

        /*
            Matrix penggunaan bahan.
            View tetap memakai $bahan->id sebagai key.
            Tetapi query ke detail_bahan_permintaan memakai $bahan->kode_bahan.
        */
        $matrix = [];

        foreach ($bahanDipakai as $bahan) {
            foreach ($items as $item) {
                $matrix[$bahan->id][$item->id] =
                    DetailBahanPermintaan::where([
                        'spk_id' => $spk->id,
                        'item_spk_id' => $item->id,
                        'bahan_id' => $bahan->kode_bahan,
                    ])->value('jumlah_permintaan') ?? 0;
            }
        }

        return view('laporan.dokubah.permintaan-bahan', compact(
            'spk',
            'items',
            'bahanDipakai',
            'bahanList',
            'matrix'
        ));
    }

    /*
        Menambahkan bahan ke tabel penggunaan.
        Dari form, bahan_ids berisi master_bahan.id.
        Yang disimpan ke detail_bahan_permintaan.bahan_id adalah kode_bahan.
    */
    public function addBahan(Request $request)
    {
        $request->validate([
            'spk_id' => 'required|exists:spk,id',
            'bahan_ids' => 'required|array|min:1',
            'bahan_ids.*' => 'required|exists:master_bahan,id',
        ]);

        $spk = Spk::with('items')->findOrFail($request->spk_id);

        if ($spk->items->isEmpty()) {
            return back()->with('error', 'Tambahkan item SPK terlebih dahulu sebelum memilih bahan.');
        }

        $jumlahBahanMasuk = 0;
        $jumlahBahanLewat = 0;

        foreach ($request->bahan_ids as $bahanId) {
            $bahan = MasterBahan::find($bahanId);

            if (!$bahan) {
                $jumlahBahanLewat++;
                continue;
            }

            /*
                Cegah bahan yang sama dimasukkan dua kali dalam SPK yang sama.
                Karena kolom bahan_id berisi kode_bahan, pengecekan pakai kode_bahan.
            */
            $sudahAda = DetailBahanPermintaan::where('spk_id', $spk->id)
                ->where('bahan_id', $bahan->kode_bahan)
                ->exists();

            if ($sudahAda) {
                $jumlahBahanLewat++;
                continue;
            }

            foreach ($spk->items as $item) {
                DetailBahanPermintaan::create([
                    'spk_id' => $spk->id,
                    'item_spk_id' => $item->id,
                    'bahan_id' => $bahan->kode_bahan,
                    'jumlah_permintaan' => 0,
                    'tanggal_permintaan' => now(),
                ]);
            }

            $jumlahBahanMasuk++;
        }

        if ($jumlahBahanMasuk === 0) {
            return back()->with('error', 'Semua bahan yang dipilih sudah ada di tabel penggunaan.');
        }

        if ($jumlahBahanLewat > 0) {
            return back()->with(
                'success',
                $jumlahBahanMasuk . ' bahan berhasil dimasukkan. ' . $jumlahBahanLewat . ' bahan dilewati karena sudah ada.'
            );
        }

        return back()->with('success', $jumlahBahanMasuk . ' bahan berhasil dimasukkan ke tabel penggunaan.');
    }

    /*
        Hapus bahan dari tabel penggunaan.
        Route/view mengirim master_bahan.id.
        Untuk hapus permintaan, sistem cari kode_bahan dulu.
    */
    public function hapusBahan($spk_id, $bahan_id)
    {
        $bahan = MasterBahan::findOrFail($bahan_id);

        /*
            Ambil semua realisasi bahan keluar untuk SPK dan bahan ini.
            Untuk tabel aktual/stok, diasumsikan masih memakai bahan_id angka.
        */
        $aktualRows = DetailBahanAktual::where('spk_id', $spk_id)
            ->where('bahan_id', $bahan->id)
            ->get();

        foreach ($aktualRows as $aktual) {
            StokBahan::where('jenis', 'keluar')
                ->where('keterangan', 'like', '%AKTUAL_ID:' . $aktual->id . '%')
                ->delete();

            $aktual->delete();
        }

        /*
            Hapus permintaan bahan.
            Karena detail_bahan_permintaan.bahan_id berisi kode_bahan,
            maka hapus berdasarkan kode_bahan.
        */
        DetailBahanPermintaan::where('spk_id', $spk_id)
            ->where('bahan_id', $bahan->kode_bahan)
            ->delete();

        return back()->with('success', 'Bahan berhasil dihapus. Data bahan keluar dan stok terkait juga sudah disesuaikan.');
    }

    /*
        Menyimpan jumlah penggunaan bahan.
        Dipanggil lewat JavaScript fetch/AJAX saat input jumlah berubah.

        Dari view, bahan_id yang dikirim adalah master_bahan.id.
        Controller mengubahnya menjadi kode_bahan sebelum disimpan.
    */
    public function store(Request $request)
    {
        $request->validate([
            'spk_id' => 'required|exists:spk,id',
            'item_spk_id' => 'required|exists:item_spk,id',
            'bahan_id' => 'required|exists:master_bahan,id',
            'jumlah_permintaan' => 'nullable|numeric|min:0',
        ]);

        $bahan = MasterBahan::findOrFail($request->bahan_id);

        DetailBahanPermintaan::updateOrCreate(
            [
                'spk_id' => $request->spk_id,
                'item_spk_id' => $request->item_spk_id,
                'bahan_id' => $bahan->kode_bahan,
            ],
            [
                'jumlah_permintaan' => $request->jumlah_permintaan ?? 0,
                'tanggal_permintaan' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan.'
        ]);
    }
}