<?php

namespace App\Http\Controllers;

use App\Models\MasterBahan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StokBahanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil input search dari URL
        $search = $request->search;

        // Ambil filter stok dari dropdown
        $filter = $request->filter ?? 'semua';

        // Ambil jumlah data per halaman
        $perPage = (int) ($request->per_page ?? 10);

        // Batasi pilihan jumlah data agar aman
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        /*
            Ambil semua data bahan beserta relasi stok.
            Search mencakup kode, nama, kategori, satuan, dan ukuran.
        */
        $bahan = MasterBahan::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_bahan', 'like', "%{$search}%")
                      ->orWhere('nama_bahan', 'like', "%{$search}%")
                      ->orWhere('kategori_bahan', 'like', "%{$search}%")
                      ->orWhere('satuan', 'like', "%{$search}%")
                      ->orWhere('ukuran', 'like', "%{$search}%");
                });
            })
            ->with('stok')
            ->latest()
            ->get();

        /*
            Hitung stok:
            stok = total masuk - total keluar
        */
        $data = $bahan->map(function ($item) {
            $masuk = $item->stok->where('jenis', 'masuk')->sum('jumlah');
            $keluar = $item->stok->where('jenis', 'keluar')->sum('jumlah');

            $stok = $masuk - $keluar;

            return [
                'kode_bahan'     => $item->kode_bahan,
                'nama_bahan'     => $item->nama_bahan,
                'kategori_bahan' => $item->kategori_bahan,
                'ukuran'         => $item->ukuran,
                'bahan_jadi'     => $item->bahan_jadi,
                'satuan'         => $item->satuan,
                'stok'           => $stok,
            ];
        });

        /*
            Filter berdasarkan status stok.
            habis   = stok <= 0
            menipis = stok 1 sampai 5
            aman    = stok lebih dari 5
        */
        if ($filter == 'habis') {
            $data = $data->filter(function ($item) {
                return $item['stok'] <= 0;
            });
        } elseif ($filter == 'menipis') {
            $data = $data->filter(function ($item) {
                return $item['stok'] > 0 && $item['stok'] <= 5;
            });
        } elseif ($filter == 'aman') {
            $data = $data->filter(function ($item) {
                return $item['stok'] > 5;
            });
        }

        /*
            Pagination manual memakai LengthAwarePaginator.
        */
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $currentItems = $data
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $data = new LengthAwarePaginator(
            $currentItems,
            $data->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('bahan.stok-bahan', compact('data'));
    }
}