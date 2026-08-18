<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilPrediksiBahan extends Model
{
    protected $table = 'hasil_prediksi_bahan';

    protected $fillable = [
        'bahan_id',
        'tahun_prediksi',
        'bulan_prediksi',
        'nilai_prediksi',
        'mae',
        'rmse',
        'r2_score',
        'mape',
    ];

    public function bahan()
    {
        return $this->belongsTo(MasterBahan::class, 'bahan_id');
    }
}