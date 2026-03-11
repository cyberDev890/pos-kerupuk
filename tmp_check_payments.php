<?php
// check_payments.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchasePayment;

$purchaseIds = [3, 4, 5, 6];
$payments = PurchasePayment::whereIn('purchase_id', $purchaseIds)->get();

echo "Associated Payments:\n";
foreach ($payments as $p) {
    echo "ID: {$p->id} | Purchase ID: {$p->purchase_id} | Amount: {$p->amount} | Note: {$p->note}\n";
}
