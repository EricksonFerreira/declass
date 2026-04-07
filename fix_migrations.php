<?php
$dir = 'database/migrations';
$files = glob("$dir/*.php");
$fixed = 0;

foreach ($files as $file) {
    $lines = file($file);

    // Step 1: Remove ALL FK constraint lines anywhere in the file
    $hadFk = false;
    $cleaned = [];
    foreach ($lines as $line) {
        if (preg_match('/Schema::(disable|enable)ForeignKeyConstraints\(\);/', $line)) {
            $hadFk = true;
            continue; // skip this line
        }
        $cleaned[] = $line;
    }

    if (!$hadFk) continue;

    // Step 2: Parse line by line, tracking brace depth
    // When we find 'public function up/down(): void' followed by '{',
    // insert disableForeignKeyConstraints after '{' and enableForeignKeyConstraints before closing '}'
    $result = [];
    $i = 0;
    $count = count($cleaned);

    while ($i < $count) {
        $line = $cleaned[$i];

        if (preg_match('/^    public function (up|down)\(\): void$/', rtrim($line))) {
            $result[] = $line;
            $i++;

            // Next line should be '    {'
            if ($i < $count && rtrim($cleaned[$i]) === '    {') {
                $result[] = $cleaned[$i]; // the {
                $i++;

                // Collect method body using brace depth counting
                $methodLines = [];
                $depth = 1;

                while ($i < $count && $depth > 0) {
                    $l = $cleaned[$i];
                    $stripped = rtrim($l);

                    // Count braces (simple, not inside strings)
                    $inSingleQ = false;
                    $inDoubleQ = false;
                    $delta = 0;
                    for ($j = 0; $j < strlen($stripped); $j++) {
                        $c = $stripped[$j];
                        if ($c === "'" && !$inDoubleQ) $inSingleQ = !$inSingleQ;
                        if ($c === '"' && !$inSingleQ) $inDoubleQ = !$inDoubleQ;
                        if (!$inSingleQ && !$inDoubleQ) {
                            if ($c === '{') $delta++;
                            if ($c === '}') $delta--;
                        }
                    }

                    $depth += $delta;

                    if ($depth === 0) {
                        // This is the closing brace of the method
                        $hasSchema = false;
                        foreach ($methodLines as $ml) {
                            if (strpos($ml, 'Schema::') !== false || strpos($ml, 'DB::') !== false) {
                                $hasSchema = true;
                                break;
                            }
                        }
                        if ($hasSchema) {
                            $result[] = "        Schema::disableForeignKeyConstraints();\n";
                            foreach ($methodLines as $ml) $result[] = $ml;
                            $result[] = "        Schema::enableForeignKeyConstraints();\n";
                        } else {
                            foreach ($methodLines as $ml) $result[] = $ml;
                        }
                        $result[] = $l; // closing }
                        $i++;
                        break;
                    }

                    $methodLines[] = $l;
                    $i++;
                }
                continue;
            }
        }

        $result[] = $line;
        $i++;
    }

    file_put_contents($file, implode('', $result));
    $fixed++;
}
echo "Fixed: $fixed files\n";
