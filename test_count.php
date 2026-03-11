<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Count 70: " . \Illuminate\Support\Facades\DB::table('transaction_details')->where('transaction_id', 70)->count() . "\n";
echo "Count 57: " . \Illuminate\Support\Facades\DB::table('transaction_details')->where('transaction_id', 57)->count() . "\n";
