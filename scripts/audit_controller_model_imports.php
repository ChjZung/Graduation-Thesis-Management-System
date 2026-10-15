<?php

/**
 * Script rà soát toàn bộ các Controller trong app/Http/Controllers
 * để phát hiện các Class / Model được gọi mà chưa có 'use App\Models\...'
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controllerDir = __DIR__ . '/../app/Http/Controllers';
$modelsDir = __DIR__ . '/../app/Models';

// Danh sách các model có trong app/Models
$existingModels = [];
foreach (scandir($modelsDir) as $f) {
    if (str_ends_with($f, '.php')) {
        $existingModels[] = str_replace('.php', '', $f);
    }
}

echo "Các Model hiện có trong app/Models (" . count($existingModels) . "):\n";
echo implode(', ', $existingModels) . "\n\n";

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerDir));
$phpFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

echo "=== KIỂM TRA " . count($phpFiles) . " CONTROLLER ===\n";

$issuesFound = [];

foreach ($phpFiles as $filePath) {
    $relativePath = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $filePath);
    $content = file_get_contents($filePath);

    // Tìm namespace của file
    preg_match('/namespace\s+([^;]+);/', $content, $nsMatch);
    $fileNamespace = $nsMatch[1] ?? '';

    // Tìm tất cả các use statements
    preg_match_all('/use\s+([^;]+);/', $content, $useMatches);
    $uses = [];
    foreach ($useMatches[1] as $u) {
        $parts = explode('\\', trim($u));
        $className = end($parts);
        // Hỗ trợ 'use ... as Alias'
        if (preg_match('/\s+as\s+(\w+)/i', $className, $asMatch)) {
            $className = $asMatch[1];
        }
        $uses[$className] = trim($u);
    }

    // Kiểm tra từng model
    foreach ($existingModels as $model) {
        // Tìm pattern Model:: hoặc new Model
        $pattern = '/(?<![a-zA-Z0-9_\\\\])' . preg_quote($model, '/') . '::/';
        if (preg_match($pattern, $content)) {
            // Kiểm tra xem model có được use hay gọi fully-qualified không
            if (!isset($uses[$model])) {
                // Kiểm tra nếu gọi \App\Models\Model:: thì hợp lệ
                $fullPattern = '/\\\\App\\\\Models\\\\' . preg_quote($model, '/') . '::/';
                // Đếm số lần gọi Model::
                preg_match_all($pattern, $content, $allCalls);
                preg_match_all($fullPattern, $content, $fullCalls);
                
                $unqualifiedCount = count($allCalls[0]) - count($fullCalls[0]);
                if ($unqualifiedCount > 0) {
                    $issuesFound[] = [
                        'file' => $relativePath,
                        'model' => $model,
                        'unqualifiedCount' => $unqualifiedCount,
                    ];
                }
            }
        }
    }
}

if (empty($issuesFound)) {
    echo "TẤT CẢ CONTROLLER ĐỀU HỢP LỆ! Không còn controller nào bị thiếu use App\\Models.\n";
} else {
    echo "PHÁT HIỆN " . count($issuesFound) . " VỊ TRÍ THIẾU IMPORT MODEL:\n";
    foreach ($issuesFound as $iss) {
        echo " [LỖI] {$iss['file']} gọi {$iss['model']}:: nhưng CHƯA use App\\Models\\{$iss['model']};\n";
    }
}
