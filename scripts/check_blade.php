<?php
$target = $argv[1] ?? 'resources/views/sinhvien/baocao/index.blade.php';
$content = file_get_contents($target);
preg_match_all('/@(if|elseif|else|endif|foreach|endforeach|forelse|empty|endforelse)\b(\s*\(.*?\))?/s', $content, $matches, PREG_OFFSET_CAPTURE);

$stack = [];
foreach ($matches[0] as $match) {
    $token = $match[0];
    $offset = $match[1];
    $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;
    
    if (preg_match('/^@(if|foreach|forelse)\b/', $token, $m)) {
        $stack[] = [$m[1], $lineNum, $token];
    } elseif (preg_match('/^@(elseif|else|empty)\b/', $token, $m)) {
        $top = end($stack);
        if (!$top) {
            echo "SYNTAX ERROR in $target at Line $lineNum: $token without matching opening directive!\n";
        }
    } elseif (preg_match('/^@(endif|endforeach|endforelse)\b/', $token, $m)) {
        if (empty($stack)) {
            echo "SYNTAX ERROR in $target at Line $lineNum: unexpected $token with empty stack!\n";
            continue;
        }
        $expected = 'end' . end($stack)[0];
        if ($m[1] !== $expected) {
            echo "MISMATCH in $target at Line $lineNum: found $token, expected @$expected (opened at Line " . end($stack)[1] . ")\n";
        }
        array_pop($stack);
    }
}

echo "File: $target -> Remaining in stack: " . count($stack) . "\n";
foreach ($stack as $s) {
    echo "Unclosed: " . $s[0] . " at Line " . $s[1] . " (" . substr($s[2], 0, 40) . ")\n";
}
