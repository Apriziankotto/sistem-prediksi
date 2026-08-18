<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stok_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_bahan_id')->constrained('master_bahan')->onDelete('cascade');
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->integer('jumlah');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_bahan');
    }
};
