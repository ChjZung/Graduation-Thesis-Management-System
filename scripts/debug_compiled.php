<?php
$lines = file('storage/framework/views/e641deacadf0ae27182d9070ed2d789d.php');
$tokens = token_get_all(file_get_contents('storage/framework/views/e641deacadf0ae27182d9070ed2d789d.php'));
$controlStack = [];
$braceStack = [];

foreach ($tokens as $token) {
    if (is_array($token)) {
        $id = $token[0];
        $text = $token[1];
        $line = $token[2];
        
        if (in_array($id, [T_IF, T_FOREACH, T_FOR, T_WHILE])) {
            $controlStack[] = [$text, $line];
        } elseif (in_array($id, [T_ELSE, T_ELSEIF])) {
            $top = end($controlStack);
            if (!$top || $top[0] !== 'if') {
                echo "INVALID $text at line $line! Current top: " . ($top ? $top[0] . ' at ' . $top[1] : 'NONE') . "\n";
            }
        } elseif (in_array($id, [T_ENDIF, T_ENDFOREACH, T_ENDFOR, T_ENDWHILE])) {
            $expected = 'end' . end($controlStack)[0];
            if ($text !== $expected) {
                echo "MISMATCH $text at line $line, expected $expected (opened at " . end($controlStack)[1] . ")\n";
            }
            array_pop($controlStack);
        }
    } else {
        if ($token === '{') {
            $braceStack[] = true;
        } elseif ($token === '}') {
            array_pop($braceStack);
        }
    }
}

echo "Remaining control stack: " . count($controlStack) . "\n";
foreach ($controlStack as $c) {
    echo "  " . $c[0] . " at line " . $c[1] . "\n";
}
echo "Remaining brace stack: " . count($braceStack) . "\n";
