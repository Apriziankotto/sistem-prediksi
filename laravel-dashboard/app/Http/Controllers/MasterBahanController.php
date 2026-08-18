<?php

namespace App\Http\Controllers;

use App\Models\MasterBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterBahanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $kategori = $request->kategori;
        $perPage = $request->per_page ?? 10;

        if (!in_array((int) $perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $kategoriList = MasterBahan::select('kategori_bahan')
            ->whereNotNull('kategori_bahan')
            ->where('kategori_bahan', '!=', '')
            ->distinct()
            ->orderBy('kategori_bahan', 'asc')
            ->pluck('kategori_bahan');

        $masterBahan = MasterBahan::query()
            ->when($kategori && $kategori != 'semua', function ($query) use ($kategori) {
                $query->where('kategori_bahan', $kategori);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_bahan', 'like', "%{$search}%")
                        ->orWhere('nama_bahan', 'like', "%{$search}%")
                        ->orWhere('kategori_bahan', 'like', "%{$search}%")
                        ->orWhere('satuan', 'like', "%{$search}%")
                        ->orWhere('ukuran', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('bahan.master-bahan', compact(
            'masterBahan',
            'kategoriList'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan'     => 'required|string|max:255',
            'kategori_bahan' => 'nullable|string|max:255',
            'kategori_baru'  => 'nullable|string|max:255',
            'satuan'         => 'required|string|max:50',
            'bahan_jadi'     => 'required|in:0,1',
            'ukuran'         => $request->bahan_jadi == '0' ? 'required|string|max:255' : 'nullable|string|max:255',
        ]);

        // Cek jika kategori baru dipilih
        $kategori = $request->kategori_bahan;
        if ($kategori === 'lainnya' && !empty($request->kategori_baru)) {
            $kategori = $request->kategori_baru;
        }

        DB::transaction(function () use ($request, $kategori) {
            $lastBahan = MasterBahan::where('kode_bahan', 'LIKE', 'B%')
                ->lockForUpdate()
                ->orderByRaw('CAST(SUBSTRING(kode_bahan, 2) AS UNSIGNED) DESC')
                ->first();

            if ($lastBahan) {
                $lastNumber = (int) substr($lastBahan->kode_bahan, 1);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $kodeBaru = 'B' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            MasterBahan::create([
                'kode_bahan'     => $kodeBaru,
                'nama_bahan'     => $request->nama_bahan,
                'kategori_bahan' => $kategori,
                'satuan'         => $request->satuan,
                'bahan_jadi'     => $request->bahan_jadi,
                'ukuran'         => $request->bahan_jadi == '0' ? $request->ukuran : null,
            ]);
        });

        return redirect()
            ->route('master-bahan.index')
            ->with('success', 'Data bahan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $master_bahan = MasterBahan::findOrFail($id);

        $request->validate([
            'nama_bahan'     => 'required|string|max:255',
            'kategori_bahan' => 'nullable|string|max:255',
            'kategori_baru'  => 'nullable|string|max:255',
            'satuan'         => 'required|string|max:50',
            'bahan_jadi'     => 'required|in:0,1',
            'ukuran'         => $request->bahan_jadi == '0' ? 'required|string|max:255' : 'nullable|string|max:255',
        ]);

        // Cek jika kategori baru dipilih
        $kategori = $request->kategori_bahan;
        if ($kategori === 'lainnya' && !empty($request->kategori_baru)) {
            $kategori = $request->kategori_baru;
        }

        $master_bahan->update([
            'nama_bahan'     => $request->nama_bahan,
            'kategori_bahan' => $kategori,
            'satuan'         => $request->satuan,
            'bahan_jadi'     => $request->bahan_jadi,
            'ukuran'         => $request->bahan_jadi == '0' ? $request->ukuran : null,
        ]);

        return redirect()
            ->route('master-bahan.index')
            ->with('success', 'Data bahan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $master_bahan = MasterBahan::findOrFail($id);
        $master_bahan->delete();

        return redirect()
            ->route('master-bahan.index')
            ->with('success', 'Data bahan berhasil dihapus.');
    }
}