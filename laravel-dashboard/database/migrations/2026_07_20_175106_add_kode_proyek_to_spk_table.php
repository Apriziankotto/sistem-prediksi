<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spk', function (Blueprint $table) {
            if (!Schema::hasColumn('spk', 'kode_proyek')) {
                $table->string('kode_proyek', 50)
                    ->nullable()
                    ->after('nama_proyek');
            }
        });
    }

    public function down(): void
    {
        Schema::table('spk', function (Blueprint $table) {
            if (Schema::hasColumn('spk', 'kode_proyek')) {
                $table->dropColumn('kode_proyek');
            }
        });
    }
};