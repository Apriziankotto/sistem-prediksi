<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_bahan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bahan')->unique();
            $table->string('nama_bahan');
            $table->string('kategori_bahan')->nullable();
            $table->string('satuan');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_bahan');
    }
};