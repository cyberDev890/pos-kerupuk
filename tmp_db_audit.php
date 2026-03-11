<?php
// db_audit.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = config('database.connections.mysql.database');
$key = "Tables_in_$dbName";

foreach ($tables as $table) {
    if (isset($table->$key)) {
        $tableName = $table->$key;
        $count = DB::table($tableName)->count();
        echo "$tableName: $count\n";
    }
}
