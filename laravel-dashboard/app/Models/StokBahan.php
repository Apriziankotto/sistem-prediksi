<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokBahan extends Model
{
    protected $table = 'stok_bahan';

    protected $fillable = [
        'master_bahan_id',
        'jenis',
        'jumlah',
        'keterangan'
    ];

    public function masterBahan()
    {
        return $this->belongsTo(MasterBahan::class);
    }
}
