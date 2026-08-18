<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('rekap_bahan_bulanan', function (Blueprint $table) {
            $table->id();

            // Periode rekap
            $table->date('tanggal');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');

            // Identitas bahan
            // Dibuat string agar aman jika IdBahan kamu berbentuk kode seperti B001, BRG001, dll.
            $table->string('kode_bahan', 50);

            // Nilai rekap bulanan
            $table->decimal('total_permintaan', 15, 2)->default(0);
            $table->decimal('total_aktual', 15, 2)->default(0);
            $table->unsignedInteger('jumlah_spk')->default(0);
            $table->unsignedInteger('total_jumlah_item')->default(0);

            // Penanda sumber data
            // import_dataset = data historis dari dataset final
            // agregasi_sistem = data baru dari transaksi SPK sistem
            $table->string('sumber_data', 30)->default('import_dataset');

            $table->timestamps();

            // Supaya satu bahan tidak dobel pada bulan yang sama
            $table->unique(
                ['tahun', 'bulan', 'kode_bahan'],
                'rekap_bulanan_unique'
            );

            // Index agar pencarian riwayat 12 bulan lebih cepat
            $table->index(['kode_bahan', 'tanggal'], 'rekap_bahan_tanggal_index');
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_bahan_bulanan');
    }
};