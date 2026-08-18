<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon; // mengelolah tanggal
use Illuminate\Support\Facades\DB;  // untuk query database

class DashboardController extends Controller
{
    public function index() // metode utama
    {
        # mencari periode prediksi terbaru untuk bahan mentah (0)
        $periodeTerbaru = DB::table('hasil_prediksi_bahan as h')
            ->join('master_bahan as mb', DB::raw('TRIM(h.kode_bahan)'), '=', DB::raw('TRIM(mb.kode_bahan)')) // TRIM utk menghilangkan spasi awal & akhir
            ->where(function($q) {
                $q->where('mb.bahan_jadi', 0)
                  ->orWhereNull('mb.bahan_jadi');
            })
            ->select('h.tahun_prediksi', 'h.bulan_prediksi')
            ->orderByDesc('h.tahun_prediksi')
            ->orderByDesc('h.bulan_prediksi')
            ->first(); // ambil baris pertama

        // simpan data terbaru
        $periodePrediksiTerbaru = '-'; 
        $tahunPrediksiTerbaru = null;
        $bulanPrediksiTerbaru = null;

        if ($periodeTerbaru) {
            $tahunPrediksiTerbaru = (int) $periodeTerbaru->tahun_prediksi;
            $bulanPrediksiTerbaru = (int) $periodeTerbaru->bulan_prediksi;

            $periodePrediksiTerbaru = Carbon::create(
                $tahunPrediksiTerbaru,
                $bulanPrediksiTerbaru,
                1
            )->translatedFormat('F Y'); // misal tahun 2026 & bulan 6 format 2026-08-01
        }


        # Rekap Stok Bahan Mentah Saat ini
        $stokSubquery = DB::table('stok_bahan')
            ->select(
                'master_bahan_id', //stok dihitung berdasarkan bahan
                // Rumus stok saat ini = total masuk - total keluar
                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(jenis) = 'masuk' THEN jumlah
                            WHEN LOWER(jenis) = 'keluar' THEN -jumlah
                            ELSE 0
                        END
                    ) AS stok_saat_ini
                ")
            )
            ->groupBy('master_bahan_id');

        # Jumlah Bahan (Hanya bahan Mentah)
        $jumlahBahan = DB::table('master_bahan')
            ->where(function($q) {
                $q->where('bahan_jadi', 0)
                  ->orWhereNull('bahan_jadi');
            })
            ->count();
        
        # Mengambil data prediksi terbaru bahan
        $prediksiTerbaru = collect(); // buat laravel collection kosong
        if ($periodeTerbaru) {
            $prediksiTerbaru = DB::table('hasil_prediksi_bahan as h')
                ->join(
                    'master_bahan as mb', DB::raw('TRIM(h.kode_bahan)'),'=', DB::raw('TRIM(mb.kode_bahan)')
                )
                // gabugnkan hasil subquery stok (left join: mungkin ada bahan yang mempunyai prediksi tetapi belum mempunyai transaksi stok, dgn LF prediksi ttp muncul)
                ->leftJoinSub( 
                    $stokSubquery,
                    'stok_rekap',
                    function ($join) {
                        $join->on('mb.id', '=', 'stok_rekap.master_bahan_id');
                    }
                )
                ->where(function($q) {
                    $q->where('mb.bahan_jadi', 0)
                      ->orWhereNull('mb.bahan_jadi');
                })
                // filter periode prediksi
                ->where('h.tahun_prediksi', $tahunPrediksiTerbaru)
                ->where('h.bulan_prediksi', $bulanPrediksiTerbaru)
                // data yang diambil
                ->select(
                    'h.kode_bahan',
                    'h.tahun_prediksi',
                    'h.bulan_prediksi',
                    'h.nilai_prediksi',
                    'h.nilai_prediksi_raw',
                    'h.updated_at',
                    'mb.nama_bahan',
                    'mb.satuan',
                    DB::raw('COALESCE(stok_rekap.stok_saat_ini, 0) AS stok_saat_ini')
                )
                ->orderBy('h.kode_bahan')
                ->get()
                // map() proses setiap item hasi query 
                ->map(function ($item) {
                    $stok = (float) ($item->stok_saat_ini ?? 0); //ambil stok (if nilai null, gunakan 0)
                    $prediksi = (float) ($item->nilai_prediksi ?? 0); //ambil prediksi

                    $status = $this->tentukanStatusPrediksi($stok, $prediksi); //ambil fungsi tentukan StatusPredksi()
                    $item->stok_saat_ini = $stok;
                    $item->nilai_prediksi = $prediksi;

                    $item->perlu_beli = max($prediksi - $stok, 0); //hitung 'perlu dibeli'. max utk buat 0 jika stok lbh besar
                    $item->jumlah_overstock = max($stok - $prediksi, 0); //hitung overstok.

                    $item->rasio_stok = $prediksi > 0 //menghitung rasio
                        ? ($stok / $prediksi) * 100
                        : 0;

                    $item->status_key = $status['key'];
                    $item->status_label = $status['label'];
                    $item->status_badge = $status['badge'];

                    return $item;
                });
        }

        # statistik daashboard
        $jumlahPerluDibeli = $prediksiTerbaru->where('status_key', 'kurang')->count(); //jumlah bahannya
        $jumlahRawan       = $prediksiTerbaru->where('status_key', 'rawan')->count();
        $jumlahAman        = $prediksiTerbaru->where('status_key', 'aman')->count();
        $jumlahOverstock   = $prediksiTerbaru->where('status_key', 'overstock')->count();
        $totalPerluDibeli  = $prediksiTerbaru->sum('perlu_beli'); //total angka dari semua bahan

        # grafik status stok
        $statusStokPrediksi = collect([
            [
                'status' => 'Kurang / Perlu Dibeli',
                'total'  => $jumlahPerluDibeli
            ],
            [
                'status' => 'Cukup, tapi Rawan',
                'total'  => $jumlahRawan
            ],
            [
                'status' => 'Aman',
                'total'  => $jumlahAman
            ],
            [
                'status' => 'Berlebih / Overstock',
                'total'  => $jumlahOverstock
            ],
        ]);

        # Data grafik aktual vs prediksi
        #total prediksi perbulan
        $prediksiBulanan = DB::table('hasil_prediksi_bahan as h')
            ->join('master_bahan as mb',DB::raw('TRIM(h.kode_bahan)'),'=',DB::raw('TRIM(mb.kode_bahan)'))
            ->where(function ($q) {
                $q->where('mb.bahan_jadi', 0)
                ->orWhere('mb.bahan_jadi', '0')
                ->orWhereNull('mb.bahan_jadi');
            })
            ->select(
                'h.tahun_prediksi as tahun',
                'h.bulan_prediksi as bulan',
                DB::raw('SUM(CAST(h.nilai_prediksi AS DECIMAL(15,2))) AS total_prediksi')
            )
            ->groupBy(
                'h.tahun_prediksi',
                'h.bulan_prediksi'
            )
            ->get();
        #total aktual perbulan
        $aktualBulanan = DB::table('detail_bahan_aktual as dba')
        ->join('master_bahan as mb', function ($join) {
            $join->on('dba.bahan_id', '=', 'mb.id')
                ->orOn(
                    DB::raw('TRIM(dba.bahan_id)'),
                    '=',
                    DB::raw('TRIM(mb.kode_bahan)')
                );
        })
        ->where(function ($q) {
            $q->where('mb.bahan_jadi', 0)
            ->orWhere('mb.bahan_jadi', '0')
            ->orWhereNull('mb.bahan_jadi');
        })
        ->select(
            DB::raw('YEAR(dba.tanggal_aktual) AS tahun'),
            DB::raw('MONTH(dba.tanggal_aktual) AS bulan'),
            DB::raw('SUM(dba.jumlah_aktual) AS total_aktual')
        )
        ->groupBy(
            DB::raw('YEAR(dba.tanggal_aktual)'),
            DB::raw('MONTH(dba.tanggal_aktual)')
        )
        ->get();

        #gabungkan total prediksi dan aktual perbulan
        $aktualVsPrediksi = $prediksiBulanan
        ->map(function ($prediksi) use ($aktualBulanan) {

            $aktual = $aktualBulanan->first(function ($item) use ($prediksi) {
                return (int) $item->tahun === (int) $prediksi->tahun
                    && (int) $item->bulan === (int) $prediksi->bulan;
            });

            return [
                'periode' => Carbon::create(
                    (int) $prediksi->tahun,
                    (int) $prediksi->bulan,
                    1
                )->translatedFormat('M Y'),

                'total_prediksi' => (float) $prediksi->total_prediksi,

                'total_aktual' => $aktual
                    ? (float) $aktual->total_aktual
                    : 0,
            ];
        })
        ->sortBy(function ($item) {
            return Carbon::createFromFormat('M Y', $item['periode']);
        })
        ->values();

        # top 10 bahan perlu dibeli
        $topPerluDibeli = $prediksiTerbaru
            ->filter(fn ($item) => $item->perlu_beli > 0)
            ->sortByDesc('perlu_beli')
            ->take(10)
            ->values()
            ->map(fn ($item) => [
                'kode_bahan' => $item->kode_bahan,
                'nama_bahan' => $item->nama_bahan,
                'perlu_beli' => $item->perlu_beli,
            ]);

        #top 10 bahan overstok
        $topOverstock = $prediksiTerbaru
            ->filter(fn ($item) => $item->status_key === 'overstock')
            ->sortByDesc('jumlah_overstock')
            ->take(10)
            ->values()
            ->map(fn ($item) => [
                'kode_bahan'       => $item->kode_bahan,
                'nama_bahan'       => $item->nama_bahan,
                'jumlah_overstock' => $item->jumlah_overstock,
            ]);

        return view('dashboard', compact(
            'periodePrediksiTerbaru',
            'jumlahBahan',
            'prediksiTerbaru',
            'jumlahPerluDibeli',
            'jumlahRawan',
            'jumlahAman',
            'jumlahOverstock',
            'totalPerluDibeli',
            'statusStokPrediksi',
            'aktualVsPrediksi',
            'topPerluDibeli',
            'topOverstock'
        ));
    }

    # menentukan status stok
    private function tentukanStatusPrediksi(float $stok, float $prediksi): array
    {
        if ($prediksi <= 0) {
            return [
                'key'   => 'tidak_ada_prediksi',
                'label' => 'Tidak Ada Prediksi',
                'badge' => 'bg-gray-100 text-gray-600',
            ];
        }

        if ($stok < $prediksi) {
            return [
                'key'   => 'kurang',
                'label' => 'Kurang / Perlu Dibeli',
                'badge' => 'bg-red-100 text-red-600',
            ];
        }
        // stok = prediksi sampai dgn kelebihan 10% dari preksi
        if ($stok < ($prediksi * 1.10)) { 
            return [
                'key'   => 'rawan',
                'label' => 'Cukup, tapi Rawan',
                'badge' => 'bg-yellow-100 text-yellow-700',
            ];
        }
        // stok 110% - 125% aman
        if ($stok <= ($prediksi * 1.25)) {
            return [
                'key'   => 'aman',
                'label' => 'Aman',
                'badge' => 'bg-green-100 text-green-600',
            ];
        }
        // lbhnya overstock
        return [
            'key'   => 'overstock',
            'label' => 'Berlebih / Overstock',
            'badge' => 'bg-purple-100 text-purple-600',
        ];
    }
}