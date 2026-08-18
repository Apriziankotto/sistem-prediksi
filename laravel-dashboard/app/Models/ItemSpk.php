<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSpk extends Model
{
    /*
        Jika nama tabel kamu item_spk, gunakan ini.
        Kalau tabel kamu ternyata item_spks, ganti menjadi:
        protected $table = 'item_spks';
    */
    protected $table = 'item_spk';

    protected $fillable = [
        'spk_id',
        'nama_item',
        'kategori_item',
        'jumlah_item',
        'keterangan',
    ];

    /*
        Relasi:
        Item SPK dimiliki oleh satu SPK.
    */
    public function spk()
    {
        return $this->belongsTo(Spk::class, 'spk_id');
    }

    /*
        Relasi:
        1 item SPK memiliki banyak detail permintaan bahan.
    */
    public function detailPermintaan()
    {
        return $this->hasMany(DetailBahanPermintaan::class, 'item_spk_id');
    }

    public function detailAktual()
    {
        return $this->hasMany(DetailBahanAktual::class, 'item_spk_id');
    }
}