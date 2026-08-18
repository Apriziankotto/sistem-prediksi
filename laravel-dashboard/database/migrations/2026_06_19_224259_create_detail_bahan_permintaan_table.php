<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_bahan_permintaan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('spk_id')
                ->constrained('spk')
                ->cascadeOnDelete();

            $table->foreignId('item_spk_id')
                ->nullable()
                ->constrained('item_spk')
                ->nullOnDelete();

            $table->foreignId('kode_bahan')
                ->constrained('master_bahan')
                ->cascadeOnDelete();

            $table->date('tanggal_permintaan')->nullable();

            $table->decimal('jumlah_permintaan', 12, 2)->default(0);

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_bahan_permintaan');
    }
};