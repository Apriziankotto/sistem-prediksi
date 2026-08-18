<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBahanPermintaan extends Model
{
    protected $table = 'detail_bahan_permintaan';

    protected $fillable = [
        'spk_id',
        'item_spk_id',
        'bahan_id',
        'tanggal_permintaan',
        'jumlah_permintaan',
        'keterangan',
    ];

    public function spk()
    {
        return $this->belongsTo(Spk::class, 'spk_id');
    }

    public function itemSpk()
    {
        return $this->belongsTo(ItemSpk::class, 'item_spk_id');
    }

    public function bahan()
    {
        return $this->belongsTo(MasterBahan::class, 'bahan_id');
    }
}