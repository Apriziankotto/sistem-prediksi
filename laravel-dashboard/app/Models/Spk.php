<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spk extends Model
{

    protected $table = 'spk';
    protected $fillable = [
        'nomor_spk',
        'nama_proyek',
        'kode_proyek',
        'tanggal_mulai',
        'tanggal_selesai',
        'bulan',
        'tahun',
        'status',
    ];

    /*
        Relasi:
        1 SPK memiliki banyak item SPK.
    */
    public function items()
    {
        return $this->hasMany(ItemSpk::class, 'spk_id');
    }

    /*
        Relasi:
        1 SPK memiliki banyak detail permintaan bahan.
    */
    public function detailPermintaan()
    {
        return $this->hasMany(DetailBahanPermintaan::class, 'spk_id');
    }

    public function detailAktual()
    {
        return $this->hasMany(DetailBahanAktual::class, 'spk_id');
    }
}