<?php

namespace App\Imports;

use App\Models\MasterBahan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterBahanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new MasterBahan([
            'kode_bahan'      => $row['id_bahan'],
            'nama_bahan'      => $row['nama_bahan'],
            'kategori_bahan'  => $row['kategori_bahan'],
            'satuan'          => $row['satuan'],
            'status'          => 'aktif',
        ]);
    }
}