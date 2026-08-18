<x-admin-layout>

<!-- data yang diterima dari controller -->
@php
    $modelInfo = $modelInfo ?? [];

    $canRetrain = $modelInfo['can_retrain'] ?? true;
    $retrainNotice = $modelInfo['retrain_notice']
        ?? 'Model siap untuk dilatih ulang jika diperlukan.';

    $metrics = collect($metrics ?? []);
    $rangeMetrics = collect($rangeMetrics ?? []);
    $bestParams = $bestParams ?? [];
    $featureImportance = collect($featureImportance ?? [])->take(5); 
    $fileStatus = collect($fileStatus ?? []);
   
    $statusModel = $modelInfo['status'] ?? 'Tidak diketahui';
    $isAktif = strtolower($statusModel) === 'aktif';
@endphp

<div class="space-y-4">

    @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- HEADER -->
    <div>
        <h1 class="text-xl font-bold text-gray-800">
            Manajemen Model
        </h1>
    </div>

    <!-- DEVELOP MODEL -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

            <div>
                <h2 class="text-base font-bold text-gray-800">
                    Develop Model
                </h2>
            </div>

            <form id="formRetrainModel"
                  action="{{ route('management-model.retrain') }}"
                  method="POST"
                  onsubmit="return handleRetrainSubmit()">
                @csrf
                <button id="btnRetrainModel" type="submit" {{ !$canRetrain ? 'disabled' : '' }} 
                 class="{{ $canRetrain ? 'bg-sky-600 hover:bg-sky-700 cursor-pointer' : 'bg-gray-400 cursor-not-allowed' }} text-white px-3.5 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1.5 transition-colors"> 
                 <i class="fa-solid {{ $canRetrain ? 'fa-rotate' : 'fa-lock' }} text-xs"></i> 
                 <span> {{ $canRetrain ? 'Training Ulang Model' : 'Training Belum Tersedia' }} </span> 
                </button>
            </form>

        </div>

        <div class="mt-3 bg-sky-50 border border-sky-100 rounded-lg p-2.5 text-xs text-sky-700">
            Lakukan training ulang untuk memperbarui file model dan evaluasi performa terbaru.
        </div>

    </div>

    <!-- STATUS MODEL -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        <div class="bg-white border border-gray-200 rounded-lg p-3.5">
            <p class="text-xs text-gray-500">Status Model</p>

            <div class="mt-1">
                @if($isAktif)
                    <span class="inline-flex px-2.5 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px] font-semibold">
                        Aktif
                    </span>
                @else
                    <span class="inline-flex px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">
                        {{ $statusModel }}
                    </span>
                @endif
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3.5">
            <p class="text-xs text-gray-500">Algoritma</p>
            <h3 class="text-sm font-bold text-gray-800 mt-0.5">
                {{ $modelInfo['nama_model'] ?? 'Random Forest Regression' }}
            </h3>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3.5">
            <p class="text-xs text-gray-500">Tanggal Training</p>
            <h3 class="text-sm font-bold text-gray-800 mt-0.5">
                {{ $modelInfo['tanggal_training'] ?? '-' }}
            </h3>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3.5">
            <p class="text-xs text-gray-500">Siklus Prediksi</p>
            <h3 class="text-sm font-bold text-gray-800 mt-0.5">
                {{ $modelInfo['siklus_prediksi'] ?? 'Bulanan' }}
            </h3>
        </div>

    </div>

    <!-- BARIS 1: HASIL EVALUASI DATA UJI & EVALUASI RANGE -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- HASIL EVALUASI DATA UJI -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 flex flex-col justify-between">

            <div>
                <div class="mb-3">
                    <h2 class="text-base font-bold text-gray-800">
                        Hasil Evaluasi Model
                    </h2>
                    <p class="text-xs text-gray-500">
                        Performa model pada data uji (testing).
                    </p>
                </div>

                <div class="overflow-x-auto border rounded-lg">
                    <table class="w-full text-xs text-left whitespace-nowrap">
                        <thead class="bg-gray-50 border-b text-gray-600 font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Dataset</th>
                                <th class="py-2.5 px-3 text-center">WAPE (%)</th>
                                <th class="py-2.5 px-3 text-center">RMSE</th>
                                <th class="py-2.5 px-3 text-center">R²</th>
                            </tr>
                        </thead>

                        <tbody class="text-gray-700 divide-y divide-gray-100">
                            @forelse($metrics as $metric)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-2 px-3 font-medium text-gray-800">
                                        {{ $metric['dataset'] ?? 'Data Uji' }}
                                    </td>

                                    <td class="py-2 px-3 text-center font-semibold text-sky-700">
                                        {{ number_format((float) ($metric['wape'] ?? 0), 2, ',', '.') }}%
                                    </td>

                                    <td class="py-2 px-3 text-center">
                                        {{ number_format((float) ($metric['rmse'] ?? 0), 4, ',', '.') }}
                                    </td>

                                    <td class="py-2 px-3 text-center">
                                        {{ number_format((float) ($metric['r2'] ?? 0), 4, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-gray-400">
                                        Data evaluasi model uji belum tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- EVALUASI BERDASARKAN RANGE AKTUAL -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">

            <div class="mb-3">
                <h2 class="text-base font-bold text-gray-800">
                    Evaluasi Berdasarkan Range Aktual
                </h2>
                <p class="text-xs text-gray-500">
                    Performa model (RMSE & WAPE) dikelompokkan berdasarkan rentang volume bahan.
                </p>
            </div>

            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-xs text-left whitespace-nowrap">
                    <thead class="bg-gray-50 border-b text-gray-600 font-semibold">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-10">No</th>
                            <th class="py-2.5 px-3">Range Aktual</th>
                            <th class="py-2.5 px-3 text-center">Jumlah</th>
                            <th class="py-2.5 px-3 text-center">RMSE</th>
                            <th class="py-2.5 px-3 text-center">WAPE (%)</th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700 divide-y divide-gray-100">
                        @forelse($rangeMetrics as $index => $row)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-1.5 px-3 text-center text-gray-400">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="py-1.5 px-3 font-medium text-sky-700">
                                    {{ $row['range'] ?? '-' }}
                                </td>

                                <td class="py-1.5 px-3 text-center">
                                    {{ number_format((int) ($row['jumlah'] ?? 0), 0, ',', '.') }}
                                </td>

                                <td class="py-1.5 px-3 text-center">
                                    {{ number_format((float) ($row['rmse'] ?? 0), 4, ',', '.') }}
                                </td>

                                <td class="py-1.5 px-3 text-center font-medium text-gray-800">
                                    {{ number_format((float) ($row['wape'] ?? 0), 2, ',', '.') }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400">
                                    Data evaluasi range aktual belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- BARIS 2: PARAMETER TERBAIK & 5 FITUR PALING BERPENGARUH -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- PARAMETER MODEL TERBAIK -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h2 class="text-base font-bold text-gray-800 mb-3">
                Parameter Terbaik Model
            </h2>

            <div class="space-y-2">
                @forelse($bestParams as $key => $value)
                    <div class="flex items-center justify-between border-b border-gray-100 pb-1.5 gap-3">
                        <span class="text-xs text-gray-500">
                            {{ $key }}
                        </span>
                        <span class="text-xs font-semibold text-gray-800 text-right font-mono">
                            @if(is_array($value))
                                {{ json_encode($value) }}
                            @elseif(is_null($value))
                                None
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                @empty
                    <div class="text-xs text-gray-400 py-4 text-center">
                        File parameter belum ditemukan.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 5 FITUR PALING BERPENGARUH -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h2 class="text-base font-bold text-gray-800 mb-3">
                5 Fitur Paling Berpengaruh
            </h2>

            <div class="space-y-3">
                @forelse($featureImportance as $fitur)
                    @php
                        $namaFitur = $fitur['fitur'] ?? '-';
                        $nilai = (float) ($fitur['nilai'] ?? 0);
                        $persen = min($nilai * 100, 100);
                    @endphp

                    <div>
                        <div class="flex justify-between text-xs mb-1 gap-2">
                            <span class="font-medium text-gray-700 truncate" title="{{ $namaFitur }}">{{ $namaFitur }}</span>
                            <span class="text-gray-500 font-mono">{{ number_format($nilai, 6, ',', '.') }}</span>
                        </div>

                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-sky-500 h-1.5 rounded-full"
                                 style="width: {{ $persen }}%">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-gray-400 py-4 text-center">
                        Data feature importance belum tersedia.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<!-- LOADING OVERLAY TRAINING -->
<div id="trainingLoadingOverlay"
     class="hidden fixed inset-0 bg-black/50 z-[9999] items-center justify-center px-4">

    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm text-center">

        <div class="mx-auto mb-4 w-10 h-10 border-3 border-sky-200 border-t-sky-600 rounded-full animate-spin"></div>

        <h2 class="text-base font-bold text-gray-800">
            Training Ulang Model Sedang Berjalan
        </h2>

        <p class="text-xs text-gray-500 mt-1.5">
            Sistem sedang mengambil data terbaru dari database, melakukan split data,
            feature engineering, dan training ulang Random Forest.
        </p>

        <div class="mt-4 bg-yellow-50 border border-yellow-100 text-yellow-700 rounded-lg p-2.5 text-xs">
            Mohon jangan menutup halaman ini sampai proses selesai. 
        </div>

    </div>

</div>

<script>
    function handleRetrainSubmit() {
        const konfirmasi = confirm(
            'Jalankan training ulang model? Proses ini dapat memakan waktu beberapa menit.'
        );

        if (!konfirmasi) {
            return false;
        }

        const overlay = document.getElementById('trainingLoadingOverlay');
        const button = document.getElementById('btnRetrainModel');

        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        if (button) {
            button.disabled = true;
            button.classList.add('opacity-70', 'cursor-not-allowed');
            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                <span>Memproses Training...</span>
            `;
        }

        return true;
    }
</script>

</x-admin-layout>