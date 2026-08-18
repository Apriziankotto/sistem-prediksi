<?php

namespace App\Http\Controllers;

use App\Models\DetailBahanAktual;
use App\Models\DetailBahanPermintaan;
use App\Models\ItemSpk;
use App\Models\MasterBahan;
use App\Models\Spk;
use App\Models\StokBahan;
use Illuminate\Http\Request;

class DetailBahanAktualController extends Controller
{
    public function index(Request $request)
    {
        $selectedSpkId = $request->spk_id;
        $selectedItemId = $request->item_spk_id;
        $search = $request->search;

        $spk = Spk::with('items')
            ->orderBy('id', 'desc')
            ->get();

        $selectedSpk = null;
        $items = collect();

        if ($selectedSpkId) {
            $selectedSpk = Spk::with(['items' => function ($query) {
                $query->orderBy('keterangan', 'asc');
            }])->find($selectedSpkId);

            if ($selectedSpk) {
                $items = $selectedSpk->items;
            }
        }

        $selectedItem = null;

        if ($selectedSpk && $selectedItemId) {
            $selectedItem = ItemSpk::where('spk_id', $selectedSpk->id)
                ->where('id', $selectedItemId)
                ->first();
        }

        $bahanRows = collect();

        if ($selectedSpk && $selectedItem) {
            $permintaan = DetailBahanPermintaan::where('spk_id', $selectedSpk->id)
                ->where('item_spk_id', $selectedItem->id)
                ->where('jumlah_permintaan', '>', 0)
                ->select('bahan_id')
                ->selectRaw('SUM(jumlah_permintaan) as jumlah_permintaan')
                ->groupBy('bahan_id')
                ->get();

            $kodeBahanList = $permintaan
                ->pluck('bahan_id')
                ->unique()
                ->filter()
                ->values();

            $bahanMap = MasterBahan::whereIn('kode_bahan', $kodeBahanList)
                ->get()
                ->keyBy('kode_bahan');

            $bahanRows = $permintaan->map(function ($row) use ($selectedSpk, $selectedItem, $bahanMap) {
                $kodeBahan = $row->bahan_id;

                $bahan = $bahanMap->get($kodeBahan);

                if (!$bahan) {
                    return null;
                }

                $sudahKeluar = DetailBahanAktual::where('spk_id', $selectedSpk->id)
                    ->where('item_spk_id', $selectedItem->id)
                    ->where('bahan_id', $bahan->kode_bahan)
                    ->sum('jumlah_aktual');

                $stokGudang = $this->hitungStokGudang($bahan->id);

                $permintaanJumlah = (float) $row->jumlah_permintaan;
                $sudahKeluarJumlah = (float) $sudahKeluar;

                $sisa = $permintaanJumlah - $sudahKeluarJumlah;

                $kelebihan = $sudahKeluarJumlah > $permintaanJumlah
                    ? $sudahKeluarJumlah - $permintaanJumlah
                    : 0;

                return [
                    'bahan' => $bahan,
                    'permintaan' => $permintaanJumlah,
                    'sudah_keluar' => $sudahKeluarJumlah,
                    'sisa' => $sisa,
                    'kelebihan' => $kelebihan,
                    'stok_gudang' => $stokGudang,
                ];
            })->filter()->values();
        }

        $riwayat = DetailBahanAktual::with(['spk', 'itemSpk'])
            ->when($selectedSpkId, function ($query) use ($selectedSpkId) {
                $query->where('spk_id', $selectedSpkId);
            })
            ->when($selectedItemId, function ($query) use ($selectedItemId) {
                $query->where('item_spk_id', $selectedItemId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                        ->orWhere('bahan_id', 'like', "%{$search}%")
                        ->orWhereHas('spk', function ($spkQuery) use ($search) {
                            $spkQuery->where('nomor_spk', 'like', "%{$search}%")
                                ->orWhere('nama_proyek', 'like', "%{$search}%");
                        })
                        ->orWhereHas('itemSpk', function ($itemQuery) use ($search) {
                            $itemQuery->where('nama_item', 'like', "%{$search}%")
                                ->orWhere('keterangan', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Manual mapping master_bahan agar nama bahan selalu terisi walaupun relasi model belum diatur
        $riwayatBahanKode = $riwayat->pluck('bahan_id')->unique()->filter()->values();
        $masterBahanMap = MasterBahan::whereIn('kode_bahan', $riwayatBahanKode)
            ->get()
            ->keyBy('kode_bahan');

        $riwayat->getCollection()->transform(function ($item) use ($masterBahanMap) {
            if (!$item->relationLoaded('bahan') || !$item->bahan) {
                $item->setRelation('bahan', $masterBahanMap->get($item->bahan_id));
            }
            return $item;
        });

        $totalTransaksi = DetailBahanAktual::count();
        $totalKeluar = DetailBahanAktual::sum('jumlah_aktual');
        $totalHariIni = DetailBahanAktual::whereDate('tanggal_aktual', now()->toDateString())->count();

        return view('laporan.keluar.index', compact(
            'spk',
            'selectedSpk',
            'selectedItem',
            'selectedSpkId',
            'selectedItemId',
            'items',
            'bahanRows',
            'riwayat',
            'totalTransaksi',
            'totalKeluar',
            'totalHariIni'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'spk_id' => 'required|exists:spk,id',
            'item_spk_id' => 'required|exists:item_spk,id',
            'bahan_id' => 'required',
            'tanggal_aktual' => 'required|date',
            'jumlah_aktual' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $item = ItemSpk::where('id', $request->item_spk_id)
            ->where('spk_id', $request->spk_id)
            ->first();

        if (!$item) {
            return back()->with('error', 'Item SPK tidak sesuai dengan SPK yang dipilih.');
        }

        $bahan = MasterBahan::where('kode_bahan', $request->bahan_id)
            ->orWhere('id', $request->bahan_id)
            ->firstOrFail();

        $permintaanJumlah = DetailBahanPermintaan::where('spk_id', $request->spk_id)
            ->where('item_spk_id', $request->item_spk_id)
            ->where('bahan_id', $bahan->kode_bahan)
            ->sum('jumlah_permintaan');

        if ($permintaanJumlah <= 0) {
            return back()->with('error', 'Bahan ini belum ada di permintaan bahan untuk item tersebut.');
        }

        $sudahKeluar = DetailBahanAktual::where('spk_id', $request->spk_id)
            ->where('item_spk_id', $request->item_spk_id)
            ->where('bahan_id', $bahan->kode_bahan)
            ->sum('jumlah_aktual');

        $jumlahSetelahKeluar = (float) $sudahKeluar + (float) $request->jumlah_aktual;

        $kelebihan = $jumlahSetelahKeluar > $permintaanJumlah
            ? $jumlahSetelahKeluar - $permintaanJumlah
            : 0;

        $keteranganFinal = $this->buatKeteranganKelebihan($request->keterangan, $kelebihan);

        $stokGudang = $this->hitungStokGudang($bahan->id);

        if ((float) $request->jumlah_aktual > $stokGudang) {
            return back()->with('error', 'Jumlah keluar melebihi stok gudang yang tersedia.');
        }

        $aktual = DetailBahanAktual::create([
            'spk_id' => $request->spk_id,
            'item_spk_id' => $request->item_spk_id,
            'bahan_id' => $bahan->kode_bahan,
            'tanggal_aktual' => $request->tanggal_aktual,
            'jumlah_aktual' => $request->jumlah_aktual,
            'keterangan' => $keteranganFinal,
        ]);

        $spkData = Spk::find($request->spk_id);

        StokBahan::create([
            'master_bahan_id' => $bahan->id,
            'jenis' => 'keluar',
            'jumlah' => $request->jumlah_aktual,
            'keterangan' => 'Keluar untuk SPK ' . ($spkData->nomor_spk ?? $request->spk_id)
                . ' Item ' . ($item->keterangan ?? '-')
                . ' - ' . ($bahan->nama_bahan ?? '-')
                . ' | AKTUAL_ID:' . $aktual->id,
        ]);

        return redirect()
            ->route('bahan-keluar.index', [
                'spk_id' => $request->spk_id,
                'item_spk_id' => $request->item_spk_id,
            ])
            ->with('success', 'Bahan keluar berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_aktual' => 'required|date',
            'jumlah_aktual' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $data = DetailBahanAktual::findOrFail($id);

        $bahan = MasterBahan::where('kode_bahan', $data->bahan_id)
            ->orWhere('id', $data->bahan_id)
            ->first();

        if (!$bahan) {
            return back()->with('error', 'Data bahan tidak ditemukan di master bahan.');
        }

        $permintaanJumlah = DetailBahanPermintaan::where('spk_id', $data->spk_id)
            ->where('item_spk_id', $data->item_spk_id)
            ->where('bahan_id', $bahan->kode_bahan)
            ->sum('jumlah_permintaan');

        if ($permintaanJumlah <= 0) {
            return back()->with('error', 'Permintaan bahan tidak ditemukan.');
        }

        $sudahKeluarSelainIni = DetailBahanAktual::where('spk_id', $data->spk_id)
            ->where('item_spk_id', $data->item_spk_id)
            ->where('bahan_id', $bahan->kode_bahan)
            ->where('id', '!=', $data->id)
            ->sum('jumlah_aktual');

        $jumlahSetelahKeluar = (float) $sudahKeluarSelainIni + (float) $request->jumlah_aktual;

        $kelebihan = $jumlahSetelahKeluar > $permintaanJumlah
            ? $jumlahSetelahKeluar - $permintaanJumlah
            : 0;

        $keteranganFinal = $this->buatKeteranganKelebihan($request->keterangan, $kelebihan);

        $stokGudangTersedia = $this->hitungStokGudang($bahan->id) + (float) $data->jumlah_aktual;

        if ((float) $request->jumlah_aktual > $stokGudangTersedia) {
            return back()->with('error', 'Jumlah keluar melebihi stok gudang yang tersedia.');
        }

        $data->update([
            'tanggal_aktual' => $request->tanggal_aktual,
            'jumlah_aktual' => $request->jumlah_aktual,
            'keterangan' => $keteranganFinal,
        ]);

        $stokKeluar = StokBahan::where('jenis', 'keluar')
            ->where('keterangan', 'like', '%AKTUAL_ID:' . $data->id . '%')
            ->first();

        if ($stokKeluar) {
            $stokKeluar->update([
                'master_bahan_id' => $bahan->id,
                'jumlah' => $request->jumlah_aktual,
            ]);
        } else {
            StokBahan::create([
                'master_bahan_id' => $bahan->id,
                'jenis' => 'keluar',
                'jumlah' => $request->jumlah_aktual,
                'keterangan' => 'Keluar bahan | AKTUAL_ID:' . $data->id,
            ]);
        }

        return redirect()
            ->route('bahan-keluar.index', [
                'spk_id' => $data->spk_id,
                'item_spk_id' => $data->item_spk_id,
            ])
            ->with('success', 'Bahan keluar berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $data = DetailBahanAktual::findOrFail($id);

        $spkId = $data->spk_id;
        $itemSpkId = $data->item_spk_id;

        StokBahan::where('jenis', 'keluar')
            ->where('keterangan', 'like', '%AKTUAL_ID:' . $data->id . '%')
            ->delete();

        $data->delete();

        return redirect()
            ->route('bahan-keluar.index', [
                'spk_id' => $spkId,
                'item_spk_id' => $itemSpkId,
            ])
            ->with('success', 'Bahan keluar berhasil dihapus.');
    }

    private function buatKeteranganKelebihan($keterangan, $kelebihan)
    {
        $keterangan = trim($keterangan ?? '');

        $keterangan = preg_replace(
            '/\s*\|?\s*Kelebihan dari permintaan:\s*[0-9\.,]+$/i',
            '',
            $keterangan
        );

        if ($kelebihan > 0) {
            $catatan = 'Kelebihan dari permintaan: ' . number_format($kelebihan, 2, ',', '.');

            if ($keterangan !== '') {
                return $keterangan . ' | ' . $catatan;
            }

            return $catatan;
        }

        return $keterangan;
    }

    private function hitungStokGudang($bahanId)
    {
        $masuk = StokBahan::where('master_bahan_id', $bahanId)
            ->where('jenis', 'masuk')
            ->sum('jumlah');

        $keluar = StokBahan::where('master_bahan_id', $bahanId)
            ->where('jenis', 'keluar')
            ->sum('jumlah');

        return (float) $masuk - (float) $keluar;
    }
}