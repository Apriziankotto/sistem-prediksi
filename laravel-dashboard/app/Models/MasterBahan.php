<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StokBahan;

class MasterBahan extends Model
{
    protected $table = 'master_bahan';

    protected $guarded = [];

    protected $fillable = [
        'kode_bahan',
        'nama_bahan',
        'kategori_bahan',
        'satuan',
        'status',
        'bahan_jadi',
        'ukuran',
    ];

    // relasi dengan permintaanbahan & aktualbahan
    public function permintaanBahan()
    {
        return $this->hasMany(DetailBahanPermintaan::class, 'bahan_id');
    }

    public function aktualBahan()
    {
        return $this->hasMany(DetailBahanAktual::class, 'bahan_id');
    }

    public function hasilPrediksi()
    {
        return $this->hasMany(HasilPrediksiBahan::class, 'bahan_id');
    }

    public function stok()
    {
        return $this->hasMany(StokBahan::class, 'master_bahan_id');
    }

}