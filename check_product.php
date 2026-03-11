<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Product::where('nama_produk', 'LIKE', '%Bawang pedas sejoli%')->first();
if ($p) {
    echo "ID: " . $p->id . "\n";
    echo "Name: " . $p->nama_produk . "\n";
    echo "Small (harga_jual): " . $p->harga_jual . "\n";
    echo "Besar (harga_jual_besar): " . $p->harga_jual_besar . "\n";
    echo "Buy (harga_beli): " . $p->harga_beli . "\n";
    echo "Unit ID: " . $p->unit_id . "\n";
} else {
    echo "Product not found\n";
}
