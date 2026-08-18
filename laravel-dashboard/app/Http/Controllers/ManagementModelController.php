<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; //menerima request
use Illuminate\Support\Facades\DB; //utk membuat query 
use Illuminate\Support\Facades\File; //utk melakukan operasi terhadap file
use Illuminate\Support\Carbon; // utk kelolah format tanggl
use Symfony\Component\Process\Process; // untuk menjalankan file eksternal

class ManagementModelController extends Controller
{
    // fungsi utama yang dijalankan ketika halaman di buka
    public function index()
    {
        // lokasi python project
        $pythonProjectPath = env(
            'PYTHON_PROJECT_PATH',
            base_path('../python-prediksi')
        );

        //menggabukan lokasi project python dgn folder hasil_random_forest
        $hasilPath = $pythonProjectPath . DIRECTORY_SEPARATOR . 'hasil_random_forest';
        $inputPath = $pythonProjectPath . DIRECTORY_SEPARATOR . 'data_ready';

        // DIBERIKAN FALLBACK NAMA FILE (CEK 'evaluasi_range_actual.xlsx' MAUPUN 'evaluasi_range_aktual.xlsx')
        $rangeFileName = File::exists($hasilPath . DIRECTORY_SEPARATOR . 'evaluasi_range_actual.xlsx')
            ? 'evaluasi_range_actual.xlsx'
            : 'evaluasi_range_aktual.xlsx';

        //daftar lokasi smeua file yg dibutuhkan
        $paths = [
            'model_final'        => $hasilPath . DIRECTORY_SEPARATOR . 'random_forest_final.pkl',
            'features'           => $inputPath . DIRECTORY_SEPARATOR . 'features.json',
            'params'             => $hasilPath . DIRECTORY_SEPARATOR . 'best_params.json',
            'feature_importance' => $hasilPath . DIRECTORY_SEPARATOR . 'feature_importance.xlsx',
            'evaluasi_test'      => $hasilPath . DIRECTORY_SEPARATOR . 'evaluasi_test.xlsx',
            'evaluasi_range'     => $hasilPath . DIRECTORY_SEPARATOR . $rangeFileName,
            'hasil_prediksi_test'=> $hasilPath . DIRECTORY_SEPARATOR . 'hasil_prediksi_test.xlsx',
            'training_script'    => $pythonProjectPath . DIRECTORY_SEPARATOR . env('PYTHON_TRAINING_SCRIPT', '00_retrain_pipeline.py'),
        ];

        // Mengecek status keberadaan file-file model
        $fileStatus = [];
        //cek apakah file ada atau tdk? hasilnya true and false. kemudian buat informasi file
        foreach ($paths as $key => $path) {
            $exists = File::exists($path);

            $fileStatus[$key] = [
                'label'      => $this->labelFile($key),
                'path'       => $path,
                'exists'     => $exists,
                'updated_at' => $exists ? date('d F Y H:i', File::lastModified($path)) : '-',
                'size'       => $exists ? $this->formatBytes(File::size($path)) : '-',
            ];
        }

        // membaca parameter terbaik & feature 
        $bestParams = $this->readJson($paths['params']) ?? [];
        $features   = $this->readJson($paths['features']) ?? [];

        // Mengambil metrik evaluasi hasil pelatihan model
        $metrics           = $this->readTestMetrics($paths['evaluasi_test']);
        $rangeMetrics      = $this->readRangeMetrics($paths['evaluasi_range']);
        $featureImportance = $this->readFeatureImportance($paths['feature_importance']);

        $tanggalTraining = $fileStatus['model_final']['updated_at'] ?? '-';

        
        // LOGIKA PENGECEKAN KELAYAKAN RETRAIN (Untuk Indikator UI)
        $canRetrain = true;
        $retrainNotice = 'Model siap untuk dilatih ulang jika diperlukan.';

        if ($fileStatus['model_final']['exists']) {
            $lastModifiedTimestamp = File::lastModified($paths['model_final']);
            $hariSejakTraining = now()->diffInDays(Carbon::createFromTimestamp($lastModifiedTimestamp));
            $minimalHariCooldown = 90; // Cooldown 3 Bulan (90 Hari)

            if ($hariSejakTraining < $minimalHariCooldown) {
                $sisaHari = $minimalHariCooldown - $hariSejakTraining;
                $canRetrain = false;
                $retrainNotice = "Model masih dalam masa optimal. Training ulang berikutnya disarankan {$sisaHari} hari lagi (Interval 3 bulan sekali).";
            }
        }

        // membuat informasi model
        $modelInfo = [
            'nama_model'       => 'Random Forest Regression',
            'algoritma'        => 'Random Forest Regression',
            'target'           => 'Total_Aktual',
            'siklus_prediksi'  => 'Bulanan',
            'metode_prediksi'  => 'Batch Prediction',
            'status'           => $fileStatus['model_final']['exists'] ? 'Aktif' : 'File model tidak ditemukan',
            'folder_model'     => $hasilPath,
            'tanggal_training' => $tanggalTraining,
            'can_retrain'      => $canRetrain,
            'retrain_notice'   => $retrainNotice,
        ];

        // mengambil training log terakhir
        $latestTrainingLog = $this->getLatestTrainingLog();

        // return vew yang akan di kirim ke blade
        return view('model.management-model', compact(
            'modelInfo',
            'metrics',
            'rangeMetrics',
            'bestParams',
            'features',
            'featureImportance',
            'fileStatus',
            'latestTrainingLog'
        ));
    }

    # function untuk melakukan retrain (training ulang data)
    public function retrain(Request $request)
    {
        set_time_limit(0); //memastikan tdk ada limit untuk php eksekusi

        $pythonProjectPath = env(
            'PYTHON_PROJECT_PATH',
            base_path('../python-prediksi')
        );

        $pythonBin = env(
            'PYTHON_BIN',
            base_path('../python-prediksi/.venv/Scripts/python.exe')
        );

        $trainingScript = env('PYTHON_TRAINING_SCRIPT', '00_retrain_pipeline.py'); //script yang akan di training
        $scriptPath     = $pythonProjectPath . DIRECTORY_SEPARATOR . $trainingScript;
        $modelFinalPath = $pythonProjectPath . DIRECTORY_SEPARATOR . 'hasil_random_forest' . DIRECTORY_SEPARATOR . 'random_forest_final.pkl'; //lokasi model final

        //validasi script python
        if (!File::exists($scriptPath)) {
            return back()->with('error', 'Script training tidak ditemukan: ' . $scriptPath);
        }
        // Mengecek apakah python executable tersedia
        if (!File::exists($pythonBin)) {
            return back()->with('error', 'Python executable tidak ditemukan: ' . $pythonBin);
        }

        // membuat folder penyimpanan log training (riwayat prooses trainig model)
        $logDir = storage_path('logs/model_training');
        if (!File::exists($logDir)) {
            File::makeDirectory($logDir, 0755, true);
        }

        $logPath = $logDir . DIRECTORY_SEPARATOR . 'training_' . date('Ymd_His') . '.log'; //membuat nama file log

        // output python yang akan tersimpan di log
        File::put($logPath, "=== MULAI TRAINING ULANG MODEL ===\n");
        File::append($logPath, "Waktu mulai : " . date('d-m-Y H:i:s') . "\n");
        File::append($logPath, "Python      : " . $pythonBin . "\n");
        File::append($logPath, "Project     : " . $pythonProjectPath . "\n");
        File::append($logPath, "Script      : " . $scriptPath . "\n\n");

        $systemRoot = getenv('SystemRoot') ?: 'C:\\Windows';
        $windir     = getenv('WINDIR') ?: 'C:\\Windows';
        $pathEnv    = getenv('PATH') ?: getenv('Path') ?: '';

        // enviroment untuk proses python
        $processEnv = [
            'SystemRoot'          => $systemRoot,
            'WINDIR'              => $windir,
            'PATH'                => $pathEnv,
            'Path'                => $pathEnv,
            'TEMP'                => sys_get_temp_dir(),
            'TMP'                 => sys_get_temp_dir(),
            'COMSPEC'             => getenv('COMSPEC') ?: 'C:\\Windows\\System32\\cmd.exe',
            'PYTHONIOENCODING'    => 'utf-8',
            'PYTHONUTF8'          => '1',
            'PYTHON_PROJECT_PATH' => $pythonProjectPath,
            'DB_HOST'             => env('DB_HOST', '127.0.0.1'),
            'DB_PORT'             => env('DB_PORT', '3306'),
            'DB_DATABASE'         => env('DB_DATABASE', 'prediksi-bahan'),
            'DB_USERNAME'         => env('DB_USERNAME', 'root'),
            'DB_PASSWORD'         => env('DB_PASSWORD', ''),
        ];

        // menjakankan python
        $process = new Process(
            [$pythonBin, $scriptPath],
            $pythonProjectPath,
            $processEnv
        );

        // maksimal proses training 2 jam
        $process->setTimeout(7200);
        $process->setIdleTimeout(null); //jika tdk selesai, maka timeout

        //simpan output python ke file log
        $process->run(function ($type, $buffer) use ($logPath) {
            File::append($logPath, $buffer);
        });

        File::append($logPath, "\nWaktu selesai: " . date('d-m-Y H:i:s') . "\n"); //simpan informasi selesai training
        File::append($logPath, "Exit Code: " . $process->getExitCode() . "\n"); 

        //if training gagal
        if (!$process->isSuccessful()) {
            $errOutput = $process->getErrorOutput();
            File::append($logPath, "\n=== TRAINING GAGAL ===\n");
            File::append($logPath, "\nSTDERR:\n" . $errOutput);

            $errorMessage = 'Training ulang model gagal.';

            return back()
                ->with('error', $errorMessage)
                ->with('training_log', File::get($logPath));
        }

        // training ulang berhasil
        File::append($logPath, "\n=== TRAINING ULANG MODEL SELESAI ===\n");

        return back()
            ->with('success', 'Training ulang model berhasil dijalankan.')
            ->with('training_log', File::get($logPath));
    }

    private function readTestMetrics($path)
    {
        $rows = $this->readExcelAssoc($path);
        if (empty($rows)) {
            return [];
        }

        $row = $rows[0];

        return [
            [
                'dataset' => 'Data Uji (Testing)',
                'wape'    => $this->toFloat($this->getValue($row, ['WAPE_%', 'WAPE', 'wape_%', 'wape', 'wape_(%)', 'wape_%'])),
                'rmse'    => $this->toFloat($this->getValue($row, ['RMSE', 'rmse'])),
                'r2'      => $this->toFloat($this->getValue($row, ['R2', 'R²', 'r2', 'r_squared'])),
            ]
        ];
    }

    // Membaca dan mengambil file excel evaluasi
    private function readRangeMetrics($path)
    {
        $rows = $this->readExcelAssoc($path);
        // cek apakah kosong
        if (empty($rows)) {
            return [];
        }

        $rangeMetrics = []; //tmpt simpan metrik
        foreach ($rows as $row) {
            // agar program fleksibel utk nama kolom excel
            $range = $this->getValue($row, ['Range_Aktual', 'range_aktual', 'Range', 'range', 'range_utama', 'range_actual']);
            if (!$range) continue; //if kosong, lewati

            // Ambil angka pertama dari string range untuk sorting (pake regex)
            preg_match('/\d+/', str_replace('.', '', $range), $matches); //1 atau lbh digit angka. str_rep utk hapus titik
            $sortKey = isset($matches[0]) ? (int)$matches[0] : 0; //buat angka utk sorting 1, 101 dst

            //elemen baru ke array
            $rangeMetrics[] = [
                'range'    => $range,
                'jumlah'   => (int) $this->getValue($row, ['Jumlah_Data', 'jumlah_data', 'Jumlah', 'jumlah', 'count']),
                'rmse'     => $this->toFloat($this->getValue($row, ['RMSE', 'rmse'])),
                'wape'     => $this->toFloat($this->getValue($row, ['WAPE_%', 'wape_%', 'WAPE', 'wape', 'wape_(%)'])),
                'sort_key' => $sortKey, // Digunakan khusus untuk mengurutkan
            ];
        }

        // usort utk urutkan berdasarkan angka min terkecil ke terbesar (Ascending)
        usort($rangeMetrics, fn ($a, $b) => $a['sort_key'] <=> $b['sort_key']); // a < b = -1, dst

        return $rangeMetrics;
    }

    //fitur paling berpengaruh
    private function readFeatureImportance($path)
    {
        $rows = $this->readExcelAssoc($path); //baca excel
        $features = [];

        foreach ($rows as $row) {
            $fitur = $this->getValue($row, ['Fitur', 'fitur', 'feature', 'Feature']);
            $nilai = $this->getValue($row, ['Importance', 'importance', 'nilai', 'Nilai']);

            if (!$fitur) continue;

            $features[] = [
                'fitur' => $fitur,
                'nilai' => $this->toFloat($nilai),
            ];
        }

        usort($features, fn ($a, $b) => $b['nilai'] <=> $a['nilai']);

        return array_slice($features, 0, 5);
    }

    # fungsi paling penting untuk memaca excel frngan PhpSpreadsheet & mengubah isi Excel menjadi array asosiatif PHP
    private function readExcelAssoc($path)
    {
        //cek file
        if (!File::exists($path) || !class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            return [];
        }

        //cek apakah file rusak dsb return []
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path); //buka excel
            $sheet       = $spreadsheet->getActiveSheet(); //ambil sheet aktif
            $rows        = $sheet->toArray(null, true, true, true); //ubah excel jadi array

            //cek lbh atau tdk
            if (count($rows) < 2) return [];

            // ambil header rows
            $headerRow = array_shift($rows);
            $headers   = [];

            foreach ($headerRow as $col => $header) {
                $headers[$col] = trim((string) $header);
            }

            //ambil data
            $data = [];
            foreach ($rows as $row) {
                $item = [];
                foreach ($headers as $col => $header) {
                    if ($header === '') continue; //header kosong tdk digunakan, di skip
                    $item[$header] = $row[$col] ?? null;
                }

                // masukkan item ke data
                if (!empty($item)) {
                    $data[] = $item;
                }
            }

            return $data;
        } catch (\Throwable $e) {
            return [];
        }
    }

    //Mencari nilai dari sebuah baris berdasarkan nama kolom yang diberikan
    private function getValue($row, $keys)
    {
        foreach ($keys as $key) {
            // cek apakah key tertentu benar-benar ada di dlm array
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        $normalizedRow = [];
        foreach ($row as $key => $value) {
            // Hilangkan karakter non-alphanumeric untuk normalisasi header
            $normalizedKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', trim((string) $key)));
            $normalizedRow[$normalizedKey] = $value;
        }

        foreach ($keys as $key) {
            $normalizedKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', trim((string) $key)));
            if (array_key_exists($normalizedKey, $normalizedRow)) {
                return $normalizedRow[$normalizedKey];
            }
        }

        return null;
    }

    //Mengubah nilai menjadi angka desimal (float) secara aman
    private function toFloat($value)
    {
        if ($value === null || $value === '') return 0;
        if (is_numeric($value)) return (float) $value;

        $value = str_replace(',', '.', (string) $value);
        $value = preg_replace('/[^0-9\.\-]/', '', $value);

        return is_numeric($value) ? (float) $value : 0;
    }

    // membaca filejson dan mengubahnya menjadi array
    private function readJson($path)
    {
        if (!File::exists($path)) return null;

        $content = File::get($path);
        $json    = json_decode($content, true);

        //cek apakah json valid
        return json_last_error() === JSON_ERROR_NONE ? $json : null;
    }

    // mengambil file log training
    private function getLatestTrainingLog()
    {
        // di logs
        $logDir = storage_path('logs/model_training');

        if (!File::exists($logDir)) return null;

        $logs = collect(File::files($logDir))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        if ($logs->isEmpty()) return null;

        return [
            'name'       => $logs[0]->getFilename(),
            'updated_at' => date('d F Y H:i', $logs[0]->getMTime()),
            'content'    => File::get($logs[0]->getPathname()),
        ];
    }

    //untuk mengubah nama internal lbh baik untuk di tampilkan di antarmuka
    private function labelFile($key)
    {
        return match ($key) {
            'model_final'         => 'Model Final Random Forest',
            'features'            => 'Daftar Fitur Model',
            'params'              => 'Parameter Terbaik',
            'feature_importance'  => 'Feature Importance',
            'evaluasi_test'       => 'Evaluasi Data Uji',
            'evaluasi_range'      => 'Evaluasi Range Aktual',
            'hasil_prediksi_test' => 'Hasil Prediksi Data Uji',
            'training_script'     => 'Script Training Ulang',
            default               => $key,
        };
    }

    //mengubah ukuran file dari byte menjadi B, KB dst
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB'];
        $base  = log($bytes, 1024);

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
}