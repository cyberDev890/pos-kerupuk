<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if (empty($model->no_transaksi)) {
                $maxId = Transaction::withTrashed()->max('id') ?? 0;
                $model->no_transaksi = 'TRX-' . date('Ymd') . '-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
