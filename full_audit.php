<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$transactions = \App\Models\Transaction::whereDate('tanggal', '>=', '2026-02-01')
    ->whereDate('tanggal', '<=', '2026-03-31')
    ->with('details.product.unit')
    ->get();

foreach ($transactions as $trx) {
    $trxNetRevenue = $trx->total_harga - $trx->biaya_kirim - $trx->biaya_tambahan;
    $trxCOGS = 0;
    $detailsLog = "";
    
    foreach ($trx->details as $d) {
        if ($d->hpp !== null && $d->conversion !== null) {
            $cogs = $d->jumlah * $d->conversion * $d->hpp;
        } else {
            $p = $d->product;
            if (!$p) { $cogs = 0; }
            else {
                $qtyInPcs = $d->jumlah;
                if ($p->unit && $p->unit->isi > 1) {
                    $priceBesar = $p->harga_jual_besar ?? ($p->harga_jual * $p->unit->isi);
                    $threshold = ($priceBesar + $p->harga_jual) / 2;
                    if ($priceBesar > 0 && $d->harga_satuan >= $threshold) {
                        $qtyInPcs = $d->jumlah * $p->unit->isi;
                    }
                }
                $cogs = $qtyInPcs * $p->harga_beli;
            }
        }
        $trxCOGS += $cogs;
        if ($d->product) {
            $detailsLog .= "    Item: {$d->product->nama_produk} | Qty: {$d->jumlah} | Price: ".number_format($d->harga_satuan)." | DetHPP: ".($d->hpp ?? 'NULL')." | DetConv: ".($d->conversion ?? 'NULL')." | CalcCOGS: ".number_format($cogs)."\n";
        }
    }
    
    $ratio = $trxCOGS > 0 ? ($trxNetRevenue / $trxCOGS) : 0;
    if ($ratio > 3 || $trxCOGS == 0) {
        echo "SUSPICIOUS TRX: {$trx->no_transaksi} | Rev: ".number_format($trxNetRevenue)." | COGS: ".number_format($trxCOGS)." | Ratio: ".number_format($ratio, 2)."\n";
        echo $detailsLog;
        echo "--------------------------------------------------\n";
    }
}
