<x-admin-layout>

<!-- menyiapkan variabel yg dikirim dari controller -->
@php
    $targetPeriode = $targetPeriode ?? null;
    $summary = $summary ?? [];

    $bulanSekarang = now()->translatedFormat('F Y');
    $periodeSaatIniDisplay = $targetPeriode
        ? \Carbon\Carbon::parse($targetPeriode['tanggal'])->subMonth()->translatedFormat('F Y')
        : $bulanSekarang;

    $periodePrediksiDisplay = $targetPeriode['nama_bulan'] ?? now()->addMonth()->translatedFormat('F Y');

    $statusAktif = request('status', 'semua');

    $jumlahBahan = $summary['jumlah_tampil'] ?? $summary['jumlah_bahan'] ?? 0;

    $tanggalProsesTerakhir = $summary['tanggal_proses_terakhir'] ?? null;

    $tanggalProsesDisplay = $tanggalProsesTerakhir
        ? \Carbon\Carbon::parse($tanggalProsesTerakhir)->translatedFormat('d F Y H:i')
        : '-';

    if ($statusAktif === 'rawan') {
        $cardStatusLabel = 'Rawan';
        $cardStatusJumlah = $summary['rawan'] ?? 0;
        $cardStatusColor = 'text-yellow-600';
    } elseif ($statusAktif === 'aman') {
        $cardStatusLabel = 'Aman';
        $cardStatusJumlah = $summary['aman'] ?? 0;
        $cardStatusColor = 'text-green-600';
    } elseif ($statusAktif === 'overstock') {
        $cardStatusLabel = 'Overstock';
        $cardStatusJumlah = $summary['overstock'] ?? 0;
        $cardStatusColor = 'text-purple-600';
    } else {
        $cardStatusLabel = 'Perlu Dibeli';
        $cardStatusJumlah = $summary['perlu_dibeli'] ?? 0;
        $cardStatusColor = 'text-red-600';
    }
@endphp

@include('prediksi.partials.notifikasi-modal')

<div class="space-y-4">

    <h1 class="text-xl font-bold text-gray-800">
        Prediksi Kebutuhan Bahan Bulanan
    </h1>

    <!-- periode prediksi -->
    <div class="bg-white rounded-lg border border-gray-200 p-4">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-base font-bold text-gray-800">
                Periode Prediksi
            </h2>
            <!-- jika button di tekan, proses akan dijalankan -->
            @if($targetPeriode)
                <form method="POST" action="{{ route('prediksi.process') }}">
                    @csrf

                    <button type="submit"
                            onclick="return confirm('Proses prediksi untuk periode {{ $periodePrediksiDisplay }}?')"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-rotate text-xs"></i>
                        Proses Prediksi
                    </button>
                </form>
            @else
                <span class="bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-xs font-medium">
                    Belum ada periode target
                </span>
            @endif
        </div>

        <!-- Menampilkan card waktu periode -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">

            <div class="border border-gray-200 rounded-lg p-3">
                <p class="text-xs text-gray-500">
                    Periode Saat Ini
                </p>

                <h3 class="text-lg font-bold text-gray-800 mt-0.5">
                    {{ $periodeSaatIniDisplay }}
                </h3>
            </div>

            <div class="border border-sky-200 bg-sky-50 rounded-lg p-3">
                <p class="text-xs text-sky-600 font-medium">
                    Periode yang Diprediksi
                </p>

                <h3 class="text-lg font-bold text-sky-700 mt-0.5">
                    {{ $periodePrediksiDisplay }}
                </h3>

                @if(!$targetPeriode)
                    <p class="text-[11px] text-sky-600 mt-1 leading-tight">
                        Proses tidak dapat dijankan karena data rekap periode yang ingin diprediksi belum tersedia
                    </p>
                @endif
            </div>

        </div>

    </div>

    <!-- Filter status -->
    <div class="bg-white p-4 rounded-lg border border-gray-200">

        <label class="text-xs font-medium text-gray-600 block mb-1.5">
            Filter Berdasarkan Status
        </label>
        <!-- ini form dgn onchange="this.form.submit() artinya begitu pengguna memilih status, form lgsg di krm  -->
        <form method="GET" action="{{ route('prediksi.index') }}"> 

            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <select name="status"
                    onchange="this.form.submit()"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

                <option value="semua" {{ request('status', 'semua') == 'semua' ? 'selected' : '' }}>
                    Semua
                </option>

                <option value="kurang" {{ request('status') == 'kurang' ? 'selected' : '' }}>
                    Kurang / Perlu Dibeli
                </option>

                <option value="rawan" {{ request('status') == 'rawan' ? 'selected' : '' }}>
                    Cukup, tapi Rawan
                </option>

                <option value="aman" {{ request('status') == 'aman' ? 'selected' : '' }}>
                    Aman
                </option>

                <option value="overstock" {{ request('status') == 'overstock' ? 'selected' : '' }}>
                    Berlebih / Overstock
                </option>

            </select>

        </form>

    </div>

    <!-- ringkasan hasil -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        <div class="bg-white border border-gray-200 rounded-lg p-3.5">
            <p class="text-xs text-gray-500">Jumlah Bahan</p>
            <h3 class="text-xl font-bold text-gray-800 mt-0.5">
                {{ $jumlahBahan }}
            </h3>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3.5">
            <p class="text-xs text-gray-500">Siklus Prediksi</p>
            <h3 class="text-xl font-bold text-gray-800 mt-0.5">
                1 bulan
            </h3>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3.5">
            <p class="text-xs text-gray-500">{{ $cardStatusLabel }}</p>
            <h3 class="text-xl font-bold {{ $cardStatusColor }} mt-0.5">
                {{ $cardStatusJumlah }}
            </h3>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3.5">
            <p class="text-xs text-gray-500">Tanggal Proses</p>
            <h3 class="text-sm font-bold text-gray-800 mt-1">
                {{ $tanggalProsesDisplay }}
            </h3>
        </div>

    </div>

    <!-- tabel hasil -->
    <div class="bg-white rounded-lg border border-gray-200 p-4">

        <div class="mb-4">
            <h2 class="text-base font-bold text-gray-800">
                Hasil Prediksi
            </h2>

            <p class="text-xs text-gray-500">
                Daftar prediksi kebutuhan bahan dan rekomendasi pembelian.
            </p>
        </div>

        <!-- form perpage dan search -->
        <form method="GET" action="{{ route('prediksi.index') }}"
              class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">

            <input type="hidden" name="status" value="{{ request('status', 'semua') }}">

            <div class="flex items-center gap-2">

                <span class="text-xs text-gray-600">
                    Tampilkan
                </span>

                <select name="per_page"
                        onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-2 py-1 text-xs h-8 focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>

                </select>

                <span class="text-xs text-gray-600">
                    data
                </span>

            </div>

            <div class="relative">

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari bahan..."
                       class="border border-gray-300 rounded-lg pl-8 pr-3 py-1 text-xs w-full sm:w-56 h-8 focus:outline-none focus:ring-2 focus:ring-sky-500">

                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>

            </div>

        </form>

        <div class="overflow-x-auto border rounded-lg">

            <table class="w-full text-xs text-left whitespace-nowrap">

                <thead class="border-b text-gray-600 bg-gray-50 font-semibold">
                    <tr>
                        <th class="py-2.5 px-3 text-center w-10">No</th>
                        <th class="py-2.5 px-3">Kode</th>
                        <th class="py-2.5 px-3">Nama Bahan</th>
                        <th class="py-2.5 px-3 text-center">Stok</th>
                        <th class="py-2.5 px-3 text-center">Prediksi</th>
                        <th class="py-2.5 px-3 text-center">Perlu Beli</th>
                        <th class="py-2.5 px-3 text-center">Kelebihan</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700 divide-y divide-gray-100">

                    @forelse($hasilPrediksi as $item)

                        @php
                            $stok = (float) ($item->stok_saat_ini ?? 0);
                            $prediksi = (float) ($item->nilai_prediksi ?? 0);
                            $perluBeli = (float) ($item->perlu_beli ?? max($prediksi - $stok, 0));
                            $kelebihan = (float) ($item->kelebihan ?? max($stok - $prediksi, 0));

                            $statusKey = $item->status_key ?? '';
                            $statusBadge = $item->status_badge ?? 'bg-gray-100 text-gray-600';
                            $statusLabel = $item->status_label ?? '-';

                            $namaBahan = $item->nama_bahan ?? '-';
                            $satuan = $item->satuan ?? '-';

                            // Tentukan warna angka kelebihan
                            $kelebihanColor = ($statusKey === 'rawan') ? 'text-yellow-600' : 'text-purple-600';
                        @endphp

                        <tr class="hover:bg-gray-50 transition-colors">

                            <td class="py-2 px-3 text-center text-gray-500">
                                {{ $hasilPrediksi->firstItem() + $loop->index }}
                            </td>

                            <td class="py-2 px-3 font-medium text-sky-700">
                                {{ $item->kode_bahan ?? '-' }}
                            </td>

                            <td class="py-2 px-3">
                                <div class="max-w-[200px]">
                                    <div class="text-gray-800 font-medium truncate"
                                         title="{{ $namaBahan }}">
                                        {{ $namaBahan }}
                                    </div>

                                    <div class="text-[11px] text-gray-400 truncate"
                                         title="{{ $satuan }}">
                                        {{ $satuan }}
                                    </div>
                                </div>
                            </td>

                            <td class="py-2 px-3 text-center">
                                {{ number_format($stok, 2, ',', '.') }}
                            </td>

                            <td class="py-2 px-3 text-center">
                                {{ number_format($prediksi, 2, ',', '.') }}
                            </td>

                            <td class="py-2 px-3 text-center font-medium {{ $perluBeli > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                {{ $perluBeli > 0 ? number_format($perluBeli, 2, ',', '.') : '-' }}
                            </td>

                            <td class="py-2 px-3 text-center font-medium">
                                @if($kelebihan > 0)
                                    <span class="{{ $kelebihanColor }}">
                                        +{{ number_format($kelebihan, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="py-2 px-3 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center py-6 text-gray-400">
                                Belum ada hasil prediksi. Pastikan data target sudah ada di rekap bulanan, lalu klik tombol Proses Prediksi.
                            </td>
                        </tr>

                    @endempty

                </tbody>

            </table>

        </div>

        <div class="mt-4">
            {{ $hasilPrediksi->links() }}
        </div>

    </div>

</div>

</x-admin-layout>