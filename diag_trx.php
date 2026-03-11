<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$no_trx = 'TRX-20260301-0046';
$trx = \App\Models\Transaction::where('no_transaksi', $no_trx)->with('details.product.unit')->first();

if (!$trx) {
    die("Transaction $no_trx not found\n");
}

echo "Transaction: $no_trx\n";
foreach ($trx->details as $d) {
    $p = $d->product;
    $u = $p->unit;
    echo "Product: {$p->nama_produk}\n";
    echo "  Qty: {$d->jumlah}\n";
    echo "  Deal Price: ".number_format($d->harga_satuan, 0, ',', '.')."\n";
    echo "  HPP Column (Detail): ".($d->hpp ?? 'NULL')."\n";
    echo "  Conversion Column (Detail): ".($d->conversion ?? 'NULL')."\n";
    echo "  Product Buy Price (Pcs): ".number_format($p->harga_beli, 0, ',', '.')."\n";
    echo "  Product Sell Small: ".number_format($p->harga_jual, 0, ',', '.')."\n";
    echo "  Product Sell Besar: ".number_format($p->harga_jual_besar, 0, ',', '.')."\n";
    if ($u) {
        echo "  Unit Isi: {$u->isi}\n";
        $priceSmall = $p->harga_jual;
        $priceBesar = $p->harga_jual_besar ?? ($priceSmall * $u->isi);
        $threshold = ($priceBesar + $priceSmall) / 2;
        echo "  Threshold: ".number_format($threshold, 0, ',', '.')."\n";
    }
}
