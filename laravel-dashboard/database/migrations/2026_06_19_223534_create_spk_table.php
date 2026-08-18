<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spk', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_spk')->unique();
            $table->string('nama_proyek');

            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');

            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            $table->enum('status', ['aktif', 'selesai', 'dibatalkan'])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk');
    }
};