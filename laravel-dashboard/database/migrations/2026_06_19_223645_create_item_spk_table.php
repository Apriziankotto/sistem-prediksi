<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_spk', function (Blueprint $table) {
            $table->id();

            $table->foreignId('spk_id')
                ->constrained('spk')
                ->cascadeOnDelete();

            $table->text('nama_item');
            $table->string('kategori_item')->nullable();
            $table->unsignedInteger('jumlah_item')->default(1);
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_spk');
    }
};