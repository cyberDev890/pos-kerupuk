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

echo "Transaction: $no_trx | Created: {$trx->created_at}\n";
foreach ($trx->details as $d) {
    echo "Product: {$d->product->nama_produk}\n";
    echo "  HPP: {$d->hpp} | Conv: {$d->conversion}\n";
    echo "  Detail Created: {$d->created_at} | Updated: {$d->updated_at}\n";
}
