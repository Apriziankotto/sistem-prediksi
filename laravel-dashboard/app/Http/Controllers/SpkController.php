<?php

namespace App\Http\Controllers;

use App\Models\Spk;
use App\Models\MasterBahan;
use App\Models\DetailBahanPermintaan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpkController extends Controller
{
    /**
     * LIST SPK
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = $request->per_page ?? 10;

        $spk = Spk::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_spk', 'like', "%{$search}%")
                        ->orWhere('nama_proyek', 'like', "%{$search}%");

                    if (\Schema::hasColumn('spk', 'kode_proyek')) {
                        $q->orWhere('kode_proyek', 'like', "%{$search}%");
                    }
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $nextNomorUrut = $this->getNextNomorUrut();

        return view('laporan.dokubah.index', compact('spk', 'nextNomorUrut'));
    }

    /**
     * STORE SPK
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_proyek' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ], [
            'nama_proyek.required' => 'Nama proyek wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Tanggal mulai tidak valid.',
            'tanggal_selesai.date' => 'Tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $tanggalMulai = $request->tanggal_mulai;

        $kodeProyek = $this->generateKodeProyek($request->nama_proyek);

        $bulan = date('m', strtotime($tanggalMulai));
        $tahun = date('Y', strtotime($tanggalMulai));
        $bulanRomawi = $this->bulanRomawi($bulan);

        $nomorUrut = $this->getNextNomorUrut();

        $nomorSpk = $this->generateNomorSpk(
            $nomorUrut,
            $kodeProyek,
            $bulanRomawi,
            $tahun
        );

        while (Spk::where('nomor_spk', $nomorSpk)->exists()) {
            $nomorUrut++;

            $nomorSpk = $this->generateNomorSpk(
                $nomorUrut,
                $kodeProyek,
                $bulanRomawi,
                $tahun
            );
        }

        $data = [
            'nomor_spk' => $nomorSpk,
            'nama_proyek' => $request->nama_proyek,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'status' => 'aktif',
        ];

        if (\Schema::hasColumn('spk', 'kode_proyek')) {
            $data['kode_proyek'] = $kodeProyek;
        }

        Spk::create($data);

        return redirect()
            ->route('permintaan-bahan.index')
            ->with('success', 'SPK berhasil dibuat dengan nomor ' . $nomorSpk);
    }

    /**
     * SHOW DETAIL SPK
     */
    public function show($id)
    {
        $spk = Spk::with('items')->findOrFail($id);

        $items = $spk->items;
        $bahan = MasterBahan::all();

        $matrix = [];

        foreach ($bahan as $b) {
            foreach ($items as $item) {
                $matrix[$b->id][$item->id] =
                    DetailBahanPermintaan::where([
                        'spk_id' => $spk->id,
                        'item_spk_id' => $item->id,
                        'bahan_id' => $b->id,
                    ])->value('jumlah_permintaan') ?? 0;
            }
        }

        return view('laporan.dokubah.permintaan-bahan', compact(
            'spk',
            'items',
            'bahan',
            'matrix'
        ));
    }

    /**
     * UPDATE SPK
     */
    public function update(Request $request, $id)
    {
        $spk = Spk::findOrFail($id);

        $request->validate([
            'nama_proyek' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ], [
            'nama_proyek.required' => 'Nama proyek wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Tanggal mulai tidak valid.',
            'tanggal_selesai.date' => 'Tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $tanggalMulai = $request->tanggal_mulai;

        $kodeProyek = $this->generateKodeProyek($request->nama_proyek);

        $bulan = date('m', strtotime($tanggalMulai));
        $tahun = date('Y', strtotime($tanggalMulai));
        $bulanRomawi = $this->bulanRomawi($bulan);

        $nomorUrutLama = $this->ambilNomorUrutDariNomorSpk($spk->nomor_spk);

        if (!$nomorUrutLama) {
            $nomorUrutLama = $spk->id;
        }

        $nomorSpkBaru = $this->generateNomorSpk(
            $nomorUrutLama,
            $kodeProyek,
            $bulanRomawi,
            $tahun
        );

        $cekNomorSama = Spk::where('nomor_spk', $nomorSpkBaru)
            ->where('id', '!=', $spk->id)
            ->exists();

        while ($cekNomorSama) {
            $nomorUrutLama++;

            $nomorSpkBaru = $this->generateNomorSpk(
                $nomorUrutLama,
                $kodeProyek,
                $bulanRomawi,
                $tahun
            );

            $cekNomorSama = Spk::where('nomor_spk', $nomorSpkBaru)
                ->where('id', '!=', $spk->id)
                ->exists();
        }

        $data = [
            'nomor_spk' => $nomorSpkBaru,
            'nama_proyek' => $request->nama_proyek,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ];

        if (\Schema::hasColumn('spk', 'kode_proyek')) {
            $data['kode_proyek'] = $kodeProyek;
        }

        $spk->update($data);

        return redirect()
            ->route('permintaan-bahan.index')
            ->with('success', 'SPK berhasil diperbarui.');
    }

    /**
     * DELETE SPK
     */
    public function destroy($id)
    {
        $spk = Spk::findOrFail($id);
        $spk->delete();

        return redirect()
            ->route('permintaan-bahan.index')
            ->with('success', 'SPK berhasil dihapus.');
    }

    /**
     * FORMAT NOMOR SPK
     */
    private function generateNomorSpk($nomorUrut, $kodeProyek, $bulanRomawi, $tahun)
    {
        return $nomorUrut . '/JANGUM/SPK/' . $kodeProyek . '/' . $bulanRomawi . '/' . $tahun;
    }

    /**
     * AMBIL NOMOR URUT BERIKUTNYA
     */
    private function getNextNomorUrut()
    {
        $spkList = Spk::select('nomor_spk')->get();

        $nomorTerbesar = 0;

        foreach ($spkList as $spk) {
            $nomor = $this->ambilNomorUrutDariNomorSpk($spk->nomor_spk);

            if ($nomor && $nomor > $nomorTerbesar) {
                $nomorTerbesar = $nomor;
            }
        }

        return $nomorTerbesar + 1;
    }

    /**
     * AMBIL ANGKA DEPAN DARI NOMOR SPK
     */
    private function ambilNomorUrutDariNomorSpk($nomorSpk)
    {
        if (!$nomorSpk) {
            return null;
        }

        $bagian = explode('/', $nomorSpk);

        if (!isset($bagian[0])) {
            return null;
        }

        return is_numeric($bagian[0]) ? (int) $bagian[0] : null;
    }

    /**
     * BULAN ANGKA KE ROMAWI
     */
    private function bulanRomawi($bulan)
    {
        $romawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $romawi[(int) $bulan] ?? 'I';
    }

    /**
     * GENERATE KODE PROYEK OTOMATIS
     */
    private function generateKodeProyek($namaProyek)
    {
        $text = strtoupper(Str::ascii($namaProyek));
        $text = preg_replace('/[^A-Z0-9\s]/', ' ', $text);

        $words = preg_split('/\s+/', trim($text));
        $words = array_values(array_filter($words));

        if (count($words) === 0) {
            return 'PRJ';
        }

        $kodeLokasi = [
            'TASIKMALAYA' => 'TSM',
            'BANDUNG' => 'BDG',
            'JAKARTA' => 'JKT',
            'SEMARANG' => 'SMG',
            'SURABAYA' => 'SBY',
            'CILEDUG' => 'CLD',
            'ARCAMANIK' => 'ARC',
            'SUKABUMI' => 'SKB',
            'PANDANARAN' => 'PDN',
            'WONOGIRI' => 'WNG',
            'LAMPUNG' => 'LPG',
            'YOGYAKARTA' => 'YK',
            'JOGJA' => 'YK',
            'BOGOR' => 'BGR',
            'BEKASI' => 'BKS',
            'TANGERANG' => 'TGR',
            'DEPOK' => 'DPK',
        ];

        $isRumahSakit = in_array('RS', $words)
            || (in_array('RUMAH', $words) && in_array('SAKIT', $words));

        if ($isRumahSakit) {
            return $this->generateKodeRumahSakit($words, $kodeLokasi);
        }

        return $this->generateKodeUmum($words, $kodeLokasi);
    }

    /**
     * GENERATE KODE UNTUK PROYEK RUMAH SAKIT
     */
    private function generateKodeRumahSakit($words, $kodeLokasi)
    {
        $filtered = array_values(array_filter($words, function ($word) {
            return !in_array($word, [
                'RS',
                'RUMAH',
                'SAKIT',
                'HOSPITAL',
                'KLINIK',
                'PROYEK',
                'PROJECT',
            ]);
        }));

        if (count($filtered) === 0) {
            return 'RS';
        }

        $namaUtama = $filtered[0];
        $lokasi = $filtered[count($filtered) - 1];

        $kodeNamaUtama = substr($namaUtama, 0, 1);
        $kodeLokasiFinal = $kodeLokasi[$lokasi] ?? $this->ambilKodeKata($lokasi);

        return 'RS' . $kodeNamaUtama . $kodeLokasiFinal;
    }

    /**
     * GENERATE KODE UNTUK PROYEK UMUM
     */
    private function generateKodeUmum($words, $kodeLokasi)
    {
        $filtered = array_values(array_filter($words, function ($word) {
            return !in_array($word, [
                'PROYEK',
                'PROJECT',
                'PT',
                'CV',
                'TBK',
                'DAN',
                'DI',
                'THE',
            ]);
        }));

        if (count($filtered) === 0) {
            $filtered = $words;
        }

        $lastWord = $filtered[count($filtered) - 1];

        if (isset($kodeLokasi[$lastWord])) {
            return $kodeLokasi[$lastWord];
        }

        if (count($filtered) === 1) {
            return $this->ambilKodeKata($filtered[0]);
        }

        $kode = '';

        foreach ($filtered as $word) {
            $kode .= substr($word, 0, 1);
        }

        return substr($kode, 0, 6) ?: 'PRJ';
    }

    /**
     * AMBIL KODE DARI SATU KATA
     */
    private function ambilKodeKata($word)
    {
        $word = strtoupper(Str::ascii($word));
        $word = preg_replace('/[^A-Z0-9]/', '', $word);

        if ($word === '') {
            return 'PRJ';
        }

        return substr($word, 0, 3);
    }
}