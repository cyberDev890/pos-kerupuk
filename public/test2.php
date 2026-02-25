<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$transactions = App\Models\Transaction::where('no_transaksi', 'like', 'ARX-%')->get();
echo "ARX count: " . $transactions->count() . "\n";
