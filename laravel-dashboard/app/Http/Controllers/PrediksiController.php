<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; //ambil data di krm pengguna ke server (search, status dll)
use Illuminate\Support\Facades\DB; //menggunakan query secara lgsg
use Illuminate\Support\Carbon; // untuk olah format tgl
use Illuminate\Pagination\LengthAwarePaginator; //membuat paginasi (membagi kebrp bagian)
use Symfony\Component\Process\Process as SymfonyProcess; //menjaankan program eksternal (prediksi menggunakan python)

class PrediksiController extends Controller
{
    // method index (mengambil data & mengirim data ke view)
    public function index(Request $request) // menerima Request (class/object) disimpan di request (variabel)
    {   
        // ambil data request dari pengguna
        $search = $request->search; 
        $statusFilter = $request->status ?? 'semua'; // if null gunakan 'semua'

        // kalau ada request, gunakan request.kalau tdk ada pake 10 sebagai nilai default
        $perPage = (int) $request->get('per_page', 10); 
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        // Subquery untuk menghitung total stok saat ini dari riwayat stok_bahan
        $stokSubquery = DB::table('stok_bahan')
            ->select(
                'master_bahan_id',
                DB::raw("
                    SUM(
                        CASE 
                            WHEN jenis = 'masuk' THEN jumlah
                            WHEN jenis = 'keluar' THEN -jumlah
                            ELSE 0
                        END
                    ) AS stok_saat_ini
                ")
            )
            ->groupBy('master_bahan_id');

        // periode target
        $targetTanggal = $this->getTargetTanggalPrediksi();
        $targetTanggal = Carbon::parse($targetTanggal)->startOfMonth();

        $tahunTarget = $targetTanggal->year;
        $bulanTarget = $targetTanggal->month;
        // Mengambil data hasil prediksi (hanya bahan mentah: bahan_jadi = 0)
        // Menggunakan inner join ke master_bahan agar bahan jadi / kode tidak valid otomatis terbuang
        $baseCollection = DB::table('hasil_prediksi_bahan as h') //TRIM utk hilangkan spasi sblm
            ->join('master_bahan as mb', DB::raw('TRIM(h.kode_bahan)'), '=', DB::raw('TRIM(mb.kode_bahan)')) 
            ->leftJoinSub($stokSubquery, 'stok_rekap', function ($join) {
                $join->on('mb.id', '=', 'stok_rekap.master_bahan_id');
            })
            //menampilkan hanya periode target prediksi
            ->where('h.tahun_prediksi', $tahunTarget)
            ->where('h.bulan_prediksi', $bulanTarget)
            // Hanya Tampilkan Bahan Mentah (bahan_jadi = 0)
            ->where(function($q) {
                $q->where('mb.bahan_jadi', 0)
                  ->orWhereNull('mb.bahan_jadi');
            })
            ->select(
                'h.*',
                'mb.nama_bahan',
                'mb.kategori_bahan',
                'mb.satuan',
                DB::raw('COALESCE(stok_rekap.stok_saat_ini, 0) AS stok_saat_ini') //coaleste (utk menangani null): stok null ubah jadi 0
            )
            // fungsi ini jalan kalau search memiliki nilai
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('h.kode_bahan', 'like', "%{$search}%")
                        ->orWhere('mb.nama_bahan', 'like', "%{$search}%")
                        ->orWhere('mb.kategori_bahan', 'like', "%{$search}%")
                        ->orWhere('mb.satuan', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('h.tahun_prediksi')
            ->orderByDesc('h.bulan_prediksi')
            ->orderBy('h.kode_bahan')
            ->get()
            // map semua data, proses satu per satu dan kembalikan menjadi kumpulan data baru
            ->map(function ($item) {
                $stok = (float) ($item->stok_saat_ini ?? 0); //ambil stok, jika null maka 0
                $prediksi = (float) ($item->nilai_prediksi ?? 0);

                $status = $this->tentukanStatusPrediksi($stok, $prediksi);

                // perbaharui nilai pada item
                $item->stok_saat_ini = $stok;
                $item->nilai_prediksi = $prediksi;
                $item->perlu_beli = max($prediksi - $stok, 0);
                $item->kelebihan = max($stok - $prediksi, 0);

                $item->status_key = $status['key'];
                $item->status_label = $status['label'];
                $item->status_badge = $status['badge'];
                $item->status_text = $status['text'];

                return $item;
            });

        // Ringkasan statistik (Hanya Menghitung Bahan Mentah)
        $summary = [
            'jumlah_bahan' => $baseCollection->count(),
            'total_prediksi' => $baseCollection->sum('nilai_prediksi'),
            'perlu_dibeli' => $baseCollection->where('status_key', 'kurang')->count(),
            'rawan' => $baseCollection->where('status_key', 'rawan')->count(),
            'aman' => $baseCollection->where('status_key', 'aman')->count(),
            'overstock' => $baseCollection->where('status_key', 'overstock')->count(),
            'tanggal_proses_terakhir' => $baseCollection->max('updated_at'),
        ];

        $hasilCollection = $baseCollection;

        if ($statusFilter !== 'semua') {
            $hasilCollection = $hasilCollection
                ->filter(fn($item) => $item->status_key === $statusFilter)
                ->values(); // merapikan index urut
        }

        $summary['jumlah_tampil'] = $hasilCollection->count(); //hitung jumlah data

        // Paginasi manual untuk data koleksi (skrg ada di page brp)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // utk ambil barang halaman skrg
        $currentItems = $hasilCollection
            // rumus utk mengambil barang dari nomor brp
            ->slice(($currentPage - 1) * $perPage, $perPage) //slice utk mengambil bagian
            ->values();

        // menampilkan sesuai permintaan 
        $hasilPrediksi = new LengthAwarePaginator( 
            $currentItems, //data yang ditampilkan di hlmn skrg
            $hasilCollection->count(), //jumlah seluruh data setelah filter
            $perPage, //brp data yg boleh ditampilan dlm 1 halaman
            $currentPage, // pengguna brp di halaman brp
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $targetPeriode = $this->getTargetPeriodePrediksi();

        return view('prediksi.index', compact(
            'hasilPrediksi',
            'targetPeriode',
            'summary',
            'statusFilter',
            'perPage'
        ));
    }

    //untuk menjalankan prediksi saat dpt request (button prediksi)
    public function predict(Request $request)
    {
        @set_time_limit(0); //time limit 0
        ini_set('max_execution_time', '600'); //maksimal eksekusi 600 menit
        ini_set('memory_limit', '1024M'); // memori limit

        // Update data pemakaian aktual untuk bulan lampau yang sudah selesai
        $hasilUpdateAktual = $this->updateAktualBulanSelesai();

        // Mengambil target tanggal prediksi
        $targetTanggal = $this->getTargetTanggalPrediksi();

        if (!$targetTanggal) {
            return redirect()
                ->route('prediksi.index')
                ->withErrors([
                    'prediksi' => 'Data target prediksi belum tersedia.'
                ]);
        }

        // Ubah tanggal menjadi tanggal pertama pada bulan tersebut.
        // dilakukan krn menggunakan periode bulanan, sehingga tanggal yang digunakan sebagai penanda periode dibuat konsisten pada awal bulan
        $targetTanggal = Carbon::parse($targetTanggal)->startOfMonth();

        // Rekap data SPK bulan target (Hanya Bahan Mentah)
        $hasilGenerateRekap = $this->generateRekapBulanTarget($targetTanggal);

        $tahunPrediksi = $targetTanggal->year;
        $bulanPrediksi = $targetTanggal->month;

        // Ambil data rekap target yang siap diprediksi (Hanya Bahan Mentah)
        $dataTarget = DB::table('rekap_bahan_bulanan as r')
            ->join('master_bahan as mb', DB::raw('TRIM(r.kode_bahan)'), '=', DB::raw('TRIM(mb.kode_bahan)'))
            ->whereDate('r.tanggal', $targetTanggal->format('Y-m-d')) //ambil data yg tglnya sama dgn bulan target
            // Ambil bahan_jadi = 0 (bahan mentah)
            ->where(function($q) {
                $q->where('mb.bahan_jadi', 0)
                  ->orWhereNull('mb.bahan_jadi');
            })
            // kolom yg diambil
            ->select(
                DB::raw('TRIM(r.kode_bahan) AS kode_bahan'),
                'r.tanggal',
                'r.tahun',
                'r.bulan',
                'r.total_permintaan',
                'r.jumlah_spk',
                'r.total_jumlah_item',
                'r.total_aktual'
            )
            ->orderBy(DB::raw('TRIM(r.kode_bahan)'))
            ->get();

        if ($dataTarget->isEmpty()) {
            return redirect()
                ->route('prediksi.index')
                ->withErrors([
                    'prediksi' => 'Data bahan mentah pada periode target prediksi tidak ditemukan.'
                ]);
        }
        // pluck artinya hanya ambil kode_bahan
        $kodeBahanTarget = $dataTarget
            ->pluck('kode_bahan')
            ->map(fn ($kode) => trim($kode))
            ->unique()
            ->values();

        // memastikan kode bahan yg diprediksi ada di data master
        $kodeBahanMaster = DB::table('master_bahan')
            ->whereIn(DB::raw('TRIM(kode_bahan)'), $kodeBahanTarget->toArray())
            ->where(function($q) {
                $q->where('bahan_jadi', 0)
                  ->orWhereNull('bahan_jadi');
            })
            ->pluck('kode_bahan')
            ->map(fn ($kode) => trim($kode))
            ->toArray();

        $kodeBahanMasterLookup = array_flip($kodeBahanMaster);
        // menyiapkan wadah
        $itemsPayload = [];
        $gagal = [];
        $dilewati = 0;

        // memproses setiap data
        foreach ($dataTarget as $row) {
            $kodeBahan = trim($row->kode_bahan);

            if (!isset($kodeBahanMasterLookup[$kodeBahan])) {
                $dilewati++;
                $gagal[] = "Kode bahan {$kodeBahan} merupakan barang jadi atau tidak ditemukan di master_bahan.";
                continue;
            }

            // data utk python (utk di kirim ke python)
            $itemsPayload[] = [
                'Tahun' => (int) $tahunPrediksi,
                'Bulan' => (int) $bulanPrediksi,
                'IdBahan' => (string) $kodeBahan,
                'Total_Permintaan' => (float) $row->total_permintaan,
                'Jumlah_SPK' => (float) $row->jumlah_spk,
                'Total_Jumlah_Item' => (float) $row->total_jumlah_item,
            ];
        }

        if (count($itemsPayload) === 0) {
            return redirect()
                ->route('prediksi.index')
                ->withErrors([
                    'prediksi' => 'Tidak ada bahan mentah yang dapat diprediksi.'
                ])
                ->with([
                    'gagal_prediksi' => $gagal,
                ]);
        }

        // Laravel kirim data ke python utk model
        $output = $this->runPythonBatchPrediction([
            'items' => $itemsPayload
        ]);

        //cek apakah python berhasil
        if (!($output['success'] ?? false)) {
            return redirect()
                ->route('prediksi.index')
                ->withErrors([
                    'prediksi' => 'Prediksi batch gagal: ' . ($output['message'] ?? 'Output Python tidak valid.')
                ]);
        }

        // ambil hasil prediksi dari python dan ubah ke laravel collection
        $results = collect($output['results'] ?? []);

        if ($results->isEmpty()) {
            return redirect()
                ->route('prediksi.index')
                ->withErrors([
                    'prediksi' => 'Output Python tidak menghasilkan data prediksi.'
                ]);
        }

        $now = now();

        //siapkan data utk dimasukkan ke database
        $upsertRows = $results->map(function ($result) use ($tahunPrediksi, $bulanPrediksi, $now) {
            return [
                'kode_bahan' => trim($result['IdBahan']),
                'tahun_prediksi' => $tahunPrediksi,
                'bulan_prediksi' => $bulanPrediksi,
                'nilai_prediksi_raw' => $result['Prediksi_Raw'],
                'nilai_prediksi' => $result['Prediksi'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray(); // ubah collection jadi array php

        DB::table('hasil_prediksi_bahan')->upsert(
            $upsertRows,
            ['kode_bahan', 'tahun_prediksi', 'bulan_prediksi'],
            ['nilai_prediksi_raw', 'nilai_prediksi', 'updated_at']
        );
        //menghitung jumlah berhasil
        $berhasil = count($upsertRows);

        return redirect()
            ->route('prediksi.index')
            ->with([
                'success' => "Generate prediksi selesai. Aktual terisi: {$hasilUpdateAktual['diisi']}. Rekap terbaca: {$hasilGenerateRekap['jumlah_data']}, dibuat: {$hasilGenerateRekap['dibuat']}, diupdate: {$hasilGenerateRekap['diupdate']}. Prediksi berhasil: {$berhasil}, dilewati/gagal: {$dilewati}.",
                'gagal_prediksi' => $gagal,
            ]);
    }

    // menentukan bulan yg diprediksi
    private function getTargetTanggalPrediksi()
    {
        // simulasi (Target Juli 2026):
        return '2026-06-01';

        // untuk mode produksi
        // return Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
    }

    private function getTargetPeriodePrediksi()
    {
        $targetTanggal = $this->getTargetTanggalPrediksi();

        if (!$targetTanggal) {
            return null;
        }

        $tanggal = Carbon::parse($targetTanggal)->startOfMonth();

        return [
            'tanggal' => $tanggal->format('Y-m-d'),
            'tahun' => $tanggal->year,
            'bulan' => $tanggal->month,
            'nama_bulan' => $tanggal->translatedFormat('F Y'),
        ];
    }

    // Untuk menentukan status prediksi
    private function tentukanStatusPrediksi(float $stok, float $prediksi): array
    {
        if ($prediksi <= 0) {
            return [
                'key' => 'tidak_ada_prediksi',
                'label' => 'Tidak Ada Prediksi',
                'badge' => 'bg-gray-100 text-gray-600',
                'text' => 'text-gray-600',
            ];
        }

        if ($stok < $prediksi) {
            return [
                'key' => 'kurang',
                'label' => 'Kurang / Perlu Dibeli',
                'badge' => 'bg-red-100 text-red-600',
                'text' => 'text-red-600',
            ];
        }

        if ($stok < ($prediksi * 1.10)) {
            return [
                'key' => 'rawan',
                'label' => 'Cukup, tapi Rawan',
                'badge' => 'bg-yellow-100 text-yellow-700',
                'text' => 'text-yellow-700',
            ];
        }

        if ($stok <= ($prediksi * 1.25)) {
            return [
                'key' => 'aman',
                'label' => 'Aman',
                'badge' => 'bg-green-100 text-green-600',
                'text' => 'text-green-600',
            ];
        }

        return [
            'key' => 'overstock',
            'label' => 'Berlebih / Overstock',
            'badge' => 'bg-purple-100 text-purple-600',
            'text' => 'text-purple-600',
        ];
    }

    // menentukan batas periode aktual dianggap selesai
    private function updateAktualBulanSelesai(): array
    {
        // ini utk mode simulasi
        $batasBulanBerjalan = '2026-07-01'; 

        // // ini untuk mode produksi
        // // Batas bulan berjalan = 1 bulan setelah target prediksi
        // $batasBulanBerjalan = Carbon::parse($targetTanggal)
        //     ->addMonth()
        //     ->startOfMonth();

        // mengambil tahun dan bulan
        $periodeAktual = DB::table('detail_bahan_aktual')
            ->whereDate('tanggal_aktual', '<', $batasBulanBerjalan)
            ->select(
                DB::raw('YEAR(tanggal_aktual) as tahun'),
                DB::raw('MONTH(tanggal_aktual) as bulan')
            )
            ->distinct()
            ->get();

        $jumlahPeriode = 0;
        $jumlahDiisi = 0;
        $jumlahTanpaAktual = 0;

        // untuk periksa bulan 1 per 1
        foreach ($periodeAktual as $periode) {
            $tanggalPeriode = Carbon::createFromDate($periode->tahun, $periode->bulan, 1)->startOfMonth(); //membuat tgl periode

            $jumlahPeriode++;

            $this->generateRekapBulanTarget($tanggalPeriode);

            // Hitung Total Pemakaian (Hanya Bahan Mentah: bahan_jadi = 0)
            $rowsAktual = DB::table('detail_bahan_aktual as dba')
                ->leftJoin('master_bahan as mb', function($join) {
                    $join->on('dba.bahan_id', '=', 'mb.id')
                        ->orOn(DB::raw('TRIM(dba.bahan_id)'), '=', DB::raw('TRIM(mb.kode_bahan)'));
                })
                ->where(function($q) {
                    $q->where('mb.bahan_jadi', 0)
                      ->orWhereNull('mb.bahan_jadi');
                })
                ->whereYear('dba.tanggal_aktual', $tanggalPeriode->year)
                ->whereMonth('dba.tanggal_aktual', $tanggalPeriode->month)
                ->select(
                    DB::raw("TRIM(COALESCE(mb.kode_bahan, dba.bahan_id)) AS kode_bahan"),
                    DB::raw("SUM(dba.jumlah_aktual) AS total_aktual")
                )
                ->groupBy(DB::raw("TRIM(COALESCE(mb.kode_bahan, dba.bahan_id))"))
                ->get();

            if ($rowsAktual->isEmpty()) {
                $jumlahTanpaAktual++;
                continue;
            }

            foreach ($rowsAktual as $row) {
                $kodeBahan = trim($row->kode_bahan);

                // cek apakah rekap sudah ada
                $existingRekap = DB::table('rekap_bahan_bulanan')
                    ->where('tahun', $tanggalPeriode->year)
                    ->where('bulan', $tanggalPeriode->month)
                    ->where('kode_bahan', $kodeBahan)
                    ->first();
                // untuk update total_aktual di rekap_bahan_bulanan
                if ($existingRekap) {
                    if (is_null($existingRekap->total_aktual)) {
                        $updated = DB::table('rekap_bahan_bulanan')
                            ->where('id', $existingRekap->id)
                            ->update([
                                'total_aktual' => (float) $row->total_aktual,
                                'updated_at'   => now(),
                            ]);
                        $jumlahDiisi += $updated;
                    }
                } else {
                    DB::table('rekap_bahan_bulanan')->insert([
                        'tanggal'           => $tanggalPeriode->format('Y-m-d'),
                        'tahun'             => $tanggalPeriode->year,
                        'bulan'             => $tanggalPeriode->month,
                        'kode_bahan'        => $kodeBahan,
                        'total_permintaan'  => 0, // ini nol karena bagian ini sdh ada
                        'jumlah_spk'        => 0,
                        'total_jumlah_item' => 0,
                        'total_aktual'      => (float) $row->total_aktual,
                        'sumber_data'       => 'agregasi_sistem',
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                    $jumlahDiisi++;
                }
            }
        }

        return [
            'periode_dicek' => $jumlahPeriode,
            'diisi'         => $jumlahDiisi,
            'tanpa_aktual'  => $jumlahTanpaAktual,
        ];
    }

    // Function untuk membuat rekap data permintaan/spk perbahan utk 1 bulan tertentu
    private function generateRekapBulanTarget(Carbon $targetTanggal): array
    {
        $tahun = $targetTanggal->year;
        $bulan = $targetTanggal->month;

        // Rekap SPK (Hanya Bahan Mentah: bahan_jadi = 0)
        $rows = DB::table('detail_bahan_permintaan as dbp')
            ->join('spk as s', 'dbp.spk_id', '=', 's.id')
            ->join('master_bahan as mb', DB::raw('TRIM(dbp.bahan_id)'), '=', DB::raw('TRIM(mb.kode_bahan)'))
            ->where('s.tahun', $tahun)
            ->where('s.bulan', $bulan)
            ->where('dbp.jumlah_permintaan', '>', 0)
            ->where(function($q) {
                $q->where('mb.bahan_jadi', 0)
                  ->orWhereNull('mb.bahan_jadi');
            })
            ->select(
                DB::raw('TRIM(dbp.bahan_id) AS kode_bahan'),
                DB::raw('SUM(dbp.jumlah_permintaan) AS total_permintaan'),
                DB::raw('COUNT(DISTINCT dbp.spk_id) AS jumlah_spk'),
                DB::raw('COUNT(DISTINCT dbp.item_spk_id) AS total_jumlah_item')
            )
            ->groupBy(DB::raw('TRIM(dbp.bahan_id)'))
            ->get();

        // mengambil daftar bahan dari master
        $masterKode = DB::table('master_bahan')
            ->where(function($q) {
                $q->where('bahan_jadi', 0)
                  ->orWhereNull('bahan_jadi');
            })
            ->pluck('kode_bahan')
            ->map(fn ($kode) => trim($kode))
            ->toArray();

        $masterLookup = array_flip($masterKode);

        $dibuat = 0;
        $diupdate = 0;
        $dilewati = 0;
        $tidakAdaMaster = 0;

        foreach ($rows as $row) {
            $kodeBahan = trim($row->kode_bahan);

            if (!isset($masterLookup[$kodeBahan])) {
                $tidakAdaMaster++;
                continue;
            }

            //cek apakah rekap sudah ada (kalau ada existing = ada)
            $existing = DB::table('rekap_bahan_bulanan')
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->where('kode_bahan', $kodeBahan)
                ->first();

            // meyiapkan data yg akan dimasukkan ke database
            $data = [
                'tanggal'           => $targetTanggal->format('Y-m-d'),
                'tahun'             => $tahun,
                'bulan'             => $bulan,
                'kode_bahan'        => $kodeBahan,
                'total_permintaan'  => (float) $row->total_permintaan,
                'jumlah_spk'        => (int) $row->jumlah_spk,
                'total_jumlah_item' => (int) $row->total_jumlah_item,
                'sumber_data'       => 'agregasi_sistem',
                'updated_at'        => now(),
            ];

            if ($existing) {
                // cek total_aktualnya ada nilai tdk, kalo tdk dilewati (null)
                if (!is_null($existing->total_aktual)) {
                    $dilewati++;
                    continue;
                }
                // update yg lain dgn tdk menngubah total_aktual
                DB::table('rekap_bahan_bulanan')
                    ->where('id', $existing->id)
                    ->update($data);

                $diupdate++;
            } else {
                $data['total_aktual'] = null;
                $data['created_at'] = now();

                DB::table('rekap_bahan_bulanan')->insert($data);

                $dibuat++;
            }
        }

        return [
            'jumlah_data'      => $rows->count(),
            'dibuat'           => $dibuat,
            'diupdate'         => $diupdate,
            'dilewati'         => $dilewati,
            'tidak_ada_master' => $tidakAdaMaster,
        ];
    }

    //function menerima data
    private function runPythonBatchPrediction(array $payload): array //Artinya function menerima $payload (payload itu data yg mau di krm ke python)
    {
        // laravel menggunakan python dari virtual environment milik project python
        $pythonBin = env(
            'PYTHON_BIN',
            base_path('../python-prediksi/.venv/Scripts/python.exe')
        );

        //folder tmpt project python berada
        $pythonPath = env(
            'PYTHON_PROJECT_PATH',
            base_path('../python-prediksi')
        );

        // nama file python yg akan di jalankan
        $scriptName = env(
            'PYTHON_BATCH_PREDICT_SCRIPT',
            'predict_laravel_batch.py'
        );

        // alamat lengkap
        $scriptPath = $pythonPath . DIRECTORY_SEPARATOR . $scriptName;

        // cek apakah python ada atau tdk
        if (!file_exists($pythonBin)) {
            return [
                'success' => false,
                'message' => 'Python executable tidak ditemukan: ' . $pythonBin,
            ];
        }

        if (!file_exists($scriptPath)) {
            return [
                'success' => false,
                'message' => 'Script batch Python tidak ditemukan: ' . $scriptPath,
            ];
        }

        // mencari lokasi windows/laragon
        $windowsRoot = getenv('SystemRoot') ?: 'C:\\Windows';

        // membuat environment variable yang akan diberikan kepada Python
        $env = [
            // lokasi sistem windows
            'SystemRoot'       => $windowsRoot,
            'SYSTEMROOT'       => $windowsRoot, 
            'WINDIR'           => $windowsRoot,
            // folder sementara yg bisa digunakan
            'TEMP'             => sys_get_temp_dir(),
            'TMP'              => sys_get_temp_dir(),
            // encoding agar outpu pyton tdk rusak
            'PYTHONIOENCODING' => 'utf-8',
            'PYTHONUTF8'       => '1',

            //Memberi tahu Windows/Python lokasi program-program yang diperlukan
            'PATH' => implode(PATH_SEPARATOR, [
                dirname($pythonBin),
                $pythonPath,
                $windowsRoot . '\\System32',
                $windowsRoot,
                $windowsRoot . '\\System32\\Wbem',
                getenv('PATH') ?: '',
            ]),
        ];

        // membuat proses python (utk menjakan predict.py)
        $process = new SymfonyProcess(
            [$pythonBin, $scriptPath],
            $pythonPath,
            $env
        );

        $process->setInput(json_encode($payload)); //data yg akan di krm di buat json (python tdk bisa proses array php)
        $process->setTimeout(600); //python diberi waktu 600 detik utk proses
        $process->run(); // menjalankan python

        // cek apakah proses berjalan
        if (!$process->isSuccessful()) {
            return [
                'success' => false,
                'message' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ];
        }
        // mengambil output yg dihasilkan python
        $rawOutput = trim($process->getOutput());
        // ubah json hasil output menjadi array php
        $output = json_decode($rawOutput, true);

        if (!$output) {
            return [
                'success' => false,
                'message' => 'Output Python tidak dapat dibaca sebagai JSON: ' . $rawOutput,
            ];
        }

        return $output;
    }
}