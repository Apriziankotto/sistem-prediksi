<x-admin-layout>

<!-- data yang dikirim oleh controller -->
@php
    $jumlahBahan = $jumlahBahan ?? 0;
    $jumlahPerluDibeli = $jumlahPerluDibeli ?? 0;
    $jumlahRawan = $jumlahRawan ?? 0;
    $jumlahOverstock = $jumlahOverstock ?? 0;
    $periodePrediksiTerbaru = $periodePrediksiTerbaru ?? '-';

    $statusStokPrediksi = collect($statusStokPrediksi ?? [])->values();
    $topPerluDibeli = collect($topPerluDibeli ?? [])->values();
    $topOverstock = collect($topOverstock ?? [])->values();
    $aktualVsPrediksi = collect($aktualVsPrediksi ?? [])->values();
@endphp

<div class="space-y-4">

    <!-- // Header -->
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

            <div>
                <h1 class="text-xl font-bold text-gray-800">
                    Dashboard Prediksi Kebutuhan Bahan Baku
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Ringkasan kondisi stok, kebutuhan pembelian, dan prediksi bahan baku.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <div class="bg-sky-50 border border-sky-200 rounded-lg px-3 py-1.5">
                    <p class="text-[10px] text-sky-600 font-medium leading-none mb-0.5">
                        Periode Prediksi
                    </p>
                    <p class="text-xs font-bold text-sky-700 leading-none">
                        {{ $periodePrediksiTerbaru }}
                    </p>
                </div>

                @if(\Illuminate\Support\Facades\Route::has('prediksi.index')) <!-- // cek router prediksi ada atau tdk -->
                    <a href="{{ route('prediksi.index') }}"
                       class="bg-sky-600 hover:bg-sky-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium inline-flex items-center justify-center gap-1.5 transition-colors">
                        <span>Lihat Prediksi</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                @endif
            </div>

        </div>
    </div>

    <!-- card ringkasan -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        <div class="bg-white border border-gray-200 rounded-xl p-3.5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Jumlah Bahan</p>
                    <h2 class="text-xl font-bold text-gray-800 mt-0.5">
                        <!-- number_format(angka, jumlah_desimal, pemisah_desimal, pemisah_ribuan) -->
                        {{ number_format($jumlahBahan, 0, ',', '.') }} 
                    </h2>
                </div>
                <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-red-100 rounded-xl p-3.5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Perlu Dibeli :</p>
                    <h2 class="text-xl font-bold text-red-600 mt-0.5">
                        {{ number_format($jumlahPerluDibeli, 0, ',', '.') }}
                    </h2>
                </div>
                <div class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-yellow-100 rounded-xl p-3.5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Cukup, tapi Rawan</p>
                    <h2 class="text-xl font-bold text-yellow-600 mt-0.5">
                        {{ number_format($jumlahRawan, 0, ',', '.') }}
                    </h2>
                </div>
                <div class="w-9 h-9 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-purple-100 rounded-xl p-3.5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Overstock</p>
                    <h2 class="text-xl font-bold text-purple-600 mt-0.5">
                        {{ number_format($jumlahOverstock, 0, ',', '.') }}
                    </h2>
                </div>
                <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Bagian utama -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-3">

        <!-- Pie chart status stok -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 xl:col-span-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Status Stok</h3>
                    <p class="text-xs text-gray-500">Komposisi status berdasarkan prediksi.</p>
                </div>
                <i class="fa-solid fa-chart-pie text-purple-600 text-sm"></i>
            </div>

            <div class="h-48 relative flex items-center justify-center">
                @if($statusStokPrediksi->sum('total') > 0)
                    <canvas id="chartStatusStok"></canvas>
                @else
                    <div class="w-full h-full border border-dashed border-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-xs">
                        Belum ada data
                    </div>
                @endif
            </div>
        </div>

        <!-- Bar chart top perlu dibeli -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 xl:col-span-8 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Top 10 Bahan Perlu Dibeli</h3>
                    <p class="text-xs text-gray-500">Prioritas pembelian dari selisih prediksi & stok.</p>
                </div>
                <i class="fa-solid fa-ranking-star text-red-600 text-sm"></i>
            </div>

            <div class="h-48 relative">
                @if($topPerluDibeli->count() > 0)
                    <canvas id="chartTopPerluBeli"></canvas>
                @else
                    <div class="w-full h-full border border-dashed border-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-xs">
                        Tidak ada bahan yang perlu dibeli
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Grafik Overstock dan Prediksi vs Aktual -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

        <!-- Grafik top bahan overstock -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Top 10 Bahan Overstock</h3>
                    <p class="text-xs text-gray-500">Kelebihan stok dibanding prediksi.</p>
                </div>
                <i class="fa-solid fa-box-open text-purple-600 text-sm"></i>
            </div>

            <div class="h-48 relative">
                @if($topOverstock->count() > 0)
                    <canvas id="chartTopOverstock"></canvas>
                @else
                    <div class="w-full h-full border border-dashed border-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-xs">
                        Tidak ada bahan overstock
                    </div>
                @endif
            </div>
        </div>

        <!-- Bar chart prediksi vs penggunaan aktual -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Aktual vs Prediksi</h3>
                    <p class="text-xs text-gray-500">Evaluasi akurasi prediksi.</p>
                </div>
                <i class="fa-solid fa-chart-line text-green-600 text-sm"></i>
            </div>

            <div class="h-48 relative">
                @if($aktualVsPrediksi->count() > 0)
                    <canvas id="chartAktualPrediksi"></canvas>
                @else
                    <div class="w-full h-full border border-dashed border-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-xs">
                        Belum ada data
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>

<!-- Memanggil library Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // mengambil data laravel ke javascript (const variavel js)
    const statusStokPrediksi = @json($statusStokPrediksi); // utk mengubah data php/laravel ke fotmat json agar dpt dibaca js
    const topPerluDibeli = @json($topPerluDibeli);
    const topOverstock = @json($topOverstock);
    const aktualVsPrediksi = @json($aktualVsPrediksi);

    // kumpulan warna yang akan digunakan
    const warna = {
        biru: '#0284c7',
        hijau: '#16a34a',
        kuning: '#ca8a04',
        merah: '#dc2626',
        ungu: '#7c3aed',
        abu: '#64748b'
    };

    // aturan default untuk grafik
    const opsiDefault = {
        responsive: true,
        maintainAspectRatio: false, //ukuran chart dpt mengikuti kontainer
        plugins: {
            // pengaturan ket pada grafik
            legend: { 
                labels: {
                    boxWidth: 8,
                    usePointStyle: true,
                    font: { size: 9 }
                }
            },
            // informasi yang muncul ketika kursor diarahkan ke bagian grafi
            tooltip: {
                backgroundColor: '#111827',
                padding: 8,
                cornerRadius: 6,
                titleFont: { size: 10 },
                bodyFont: { size: 10 }
            }
        },
        // mengatur sumbu x dan y
        scales: {
            y: {
                beginAtZero: true, 
                grid: { color: '#f1f5f9' },
                ticks: { color: '#94a3b8', font: { size: 9 } }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8', font: { size: 9 } }
            }
        }
    };

    // Chart Status Stok
    if (document.getElementById('chartStatusStok')) {
        new Chart(document.getElementById('chartStatusStok'), {
            type: 'doughnut',
            data: {
                labels: statusStokPrediksi.map(item => item.status), //mengambil nilai status ('Perlu dibeli', 'rawan', dst)
                datasets: [{
                    data: statusStokPrediksi.map(item => Number(item.total ?? 0)), //if nilai null maka dibuat jadi 0
                    backgroundColor: [warna.merah, warna.kuning, warna.hijau, warna.ungu], //warna tiap status
                    borderColor: '#ffffff', //garis putih utk memisahkan bagian
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', //ukuran lubang ditengah
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 6, usePointStyle: true, font: { size: 9 } }
                    }
                }
            }
        });
    }

    // Chart top perlu dibeli
    if (document.getElementById('chartTopPerluBeli')) {
        new Chart(document.getElementById('chartTopPerluBeli'), {
            type: 'bar',
            data: {
                labels: topPerluDibeli.map(item => item.kode_bahan ?? '-'), //ambil kode bahan, jika tdk ada di kasi -
                datasets: [{
                    label: 'Perlu Dibeli',
                    data: topPerluDibeli.map(item => Number(item.perlu_beli ?? 0)),
                    backgroundColor: warna.merah,
                    borderRadius: 4,
                    barThickness: 10
                }]
            },
            options: {
                ...opsiDefault,
                indexAxis: 'y' // bar dibuat horizontal
            }
        });
    }

    // Chart top overstock
    if (document.getElementById('chartTopOverstock')) {
        new Chart(document.getElementById('chartTopOverstock'), {
            type: 'bar',
            data: {
                labels: topOverstock.map(item => item.kode_bahan ?? '-'),
                datasets: [{
                    label: 'Jumlah Overstock',
                    data: topOverstock.map(item => Number(item.jumlah_overstock ?? 0)),
                    backgroundColor: warna.ungu,
                    borderRadius: 4,  //sudut batang garis melengkung
                    barThickness: 14 //ketebalan (batang grafik 12 pixel)
                }]
            },
            options: opsiDefault
        });
    }

    // chart aktual vs prediksi
    if (document.getElementById('chartAktualPrediksi')) {
        new Chart(document.getElementById('chartAktualPrediksi'), {
            type: 'line',
            data: {
                labels: aktualVsPrediksi.map(item => item.periode),
                datasets: [
                    {
                        label: 'Aktual',
                        data: aktualVsPrediksi.map(item => Number(item.total_aktual ?? 0)),
                        borderColor: warna.biru,
                        backgroundColor: warna.biru,
                        borderWidth: 2,
                        tension: 0.3, // garis melengkung halus
                        pointRadius: 3, //ukuran titik grafik
                        pointHoverRadius: 6,
                        fill: false // area dibawah garis tdk di isi warna
                    },
                    {
                        label: 'Prediksi',
                        data: aktualVsPrediksi.map(item => Number(item.total_prediksi ?? 0)),
                        borderColor: warna.hijau,
                        backgroundColor: warna.hijau,
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        fill: false
                    }
                ]
            },
            options: opsiDefault
        });
    }
</script>

</x-admin-layout>