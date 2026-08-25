<?php

function fixDirectory($dir) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $count = 0;
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $path = $file->getRealPath();
            $content = file_get_contents($path);
            $newContent = $content;
            
            // Remove readonly class
            $newContent = preg_replace('/\b(final\s+)?readonly\s+class\b/i', '$1class', $newContent);
            // Comment out #[Override]
            $newContent = str_replace('#[Override]', '// #[Override]', $newContent);
            
            if ($newContent !== $content) {
                file_put_contents($path, $newContent);
                $count++;
            }
        }
    }
    echo "Fixed $count files in $dir\n";
}

fixDirectory(__DIR__ . '/vendor');
