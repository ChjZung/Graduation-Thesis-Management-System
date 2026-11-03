<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dbFile = storage_path('app/private/ai_limits.sqlite');
if (file_exists($dbFile)) {
    echo "ai_limits.sqlite exists, size: " . filesize($dbFile) . "\n";
    $db = new PDO('sqlite:' . $dbFile);
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        echo "Table: {$t}\n";
        $rows = $db->query("SELECT * FROM {$t}")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            echo "  " . json_encode($r) . "\n";
        }
    }
} else {
    echo "ai_limits.sqlite does NOT exist.\n";
}
