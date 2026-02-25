<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$transactions = App\Models\Transaction::orderByDesc('id')->take(5)->get();
foreach($transactions as $t) {
    echo $t->id . ' | ' . $t->no_transaksi . ' | Cust: ' . $t->customer_id . ' | Total: ' . $t->total_harga . ' | Debt: ' . $t->remaining_debt . "\n";
}
