<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::where('nama_produk', 'LIKE', '%Bawang%')->get();
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->nama_produk} | Small: {$p->harga_jual} | Besar: {$p->harga_jual_besar}\n";
}
