<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
        'sku',
        'nama_produk',
        'harga_jual',
        'harga_jual_besar',
        'harga_beli',
        'kategori_id',
        'unit_id',
        'stok',
        'stok_gudang',
        'stok_min',
        'is_active',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public static function nomorSku()
    {   $prefix = 'SKU';
        $maxID = self::withTrashed()->max('id');
        $sku = $prefix . str_pad($maxID + 1, 5, '0', STR_PAD_LEFT);
        return $sku;
    }
    
    public function mutations()
    {
        return $this->hasMany(StockMutation::class);
    }
}
