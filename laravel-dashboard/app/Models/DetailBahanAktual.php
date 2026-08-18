<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBahanAktual extends Model
{
    protected $table = 'detail_bahan_aktual';

    protected $fillable = [
        'spk_id',
        'item_spk_id',
        'bahan_id',
        'tanggal_aktual',
        'jumlah_aktual',
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