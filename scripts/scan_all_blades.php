<?php
$dirs = [
    'resources/views/sinhvien',
    'resources/views/layouts',
    'resources/views/partials',
];

foreach ($dirs as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            passthru("php scripts/check_blade.php " . escapeshellarg($file->getPathname()));
        }
    }
}
