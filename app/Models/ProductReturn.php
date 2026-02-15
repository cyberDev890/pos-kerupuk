<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReturn extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(ReturnDetail::class, 'product_return_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $maxId = ProductReturn::withTrashed()->max('id') ?? 0;
            $model->no_retur = 'RET-' . date('Ymd') . '-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}
