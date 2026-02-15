<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'nama_satuan',
        'satuan_kecil',
        'satuan_besar',
        'isi',
    ];
}
