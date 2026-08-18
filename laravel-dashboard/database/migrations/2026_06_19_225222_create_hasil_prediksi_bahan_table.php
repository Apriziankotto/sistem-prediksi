<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_prediksi_bahan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bahan_id')
                ->constrained('master_bahan')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('tahun_prediksi');
            $table->unsignedTinyInteger('bulan_prediksi');

            $table->decimal('nilai_prediksi', 12, 2)->default(0);

            $table->decimal('mae', 12, 4)->nullable();
            $table->decimal('rmse', 12, 4)->nullable();
            $table->decimal('r2_score', 8, 4)->nullable();
            $table->decimal('mape', 8, 4)->nullable();

            $table->timestamps();

            $table->unique(
                ['bahan_id', 'tahun_prediksi', 'bulan_prediksi'],
                'uniq_prediksi_bahan_bulan'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_prediksi_bahan');
    }
};