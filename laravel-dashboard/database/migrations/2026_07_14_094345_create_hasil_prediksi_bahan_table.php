<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel hasil_prediksi_bahan.
     */
    public function up(): void
    {
        Schema::create('hasil_prediksi_bahan', function (Blueprint $table) {
            $table->id();

            // Disamakan dengan master_bahan.kode_bahan
            $table->string('kode_bahan', 255);

            // Periode prediksi
            $table->unsignedSmallInteger('tahun_prediksi');
            $table->unsignedTinyInteger('bulan_prediksi');

            // Hasil prediksi dari Python
            $table->decimal('nilai_prediksi_raw', 15, 6)->nullable();
            $table->decimal('nilai_prediksi', 15, 2)->default(0);

            $table->timestamps();

            // Satu bahan hanya boleh punya satu hasil prediksi pada bulan yang sama
            $table->unique(
                ['kode_bahan', 'tahun_prediksi', 'bulan_prediksi'],
                'hasil_prediksi_unique'
            );

            $table->index(
                ['tahun_prediksi', 'bulan_prediksi'],
                'hasil_prediksi_periode_index'
            );

            // Relasi ke master_bahan
            $table->foreign('kode_bahan')
                ->references('kode_bahan')
                ->on('master_bahan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Menghapus tabel jika rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_prediksi_bahan');
    }
};