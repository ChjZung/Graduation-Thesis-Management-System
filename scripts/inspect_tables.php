<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DANH SACH CAC BANG TRONG DATABASE ===\n";
$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$colName = "Tables_in_" . $dbName;

$allTables = [];
print_r(DB::select('DESCRIBE giaovu'));
print_r(DB::table('taikhoan')->where('MaVaiTro', 'VT01')->get());

