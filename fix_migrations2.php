<?php
/**
 * Adds Schema::hasTable() guards before every Schema::table('tablename', ...)
 * call that doesn't already have one in the same method scope.
 */
$dir = 'database/migrations';
$files = glob("$dir/*.php");
$fixed = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    // Find all Schema::table('tablename', ...) calls and add hasTable guard if missing
    $content = preg_replace_callback(
        '/( +)(Schema::table\(\'([^\']+)\',\s*function)/m',
        function ($m) {
            $indent  = $m[1];
            $call    = $m[2];
            $table   = $m[3];
            $guard   = "if (!Schema::hasTable('{$table}')) { return; }";

            // Check if this line is preceded by a hasTable check for the same table
            // We'll do a simple look-behind by returning with a marker
            return $m[0]; // return unchanged — we handle full-method replacement below
        },
        $content
    );

    // Re-parse line by line and inject guards before Schema::table() if missing in that method
    $lines = file($file);
    $result = [];
    $i = 0;
    $count = count($lines);
    $modified = false;

    while ($i < $count) {
        $line = $lines[$i];

        // Detect method opening: 'public function up/down(): void'
        if (preg_match('/^    public function (up|down)\(\): void$/', rtrim($line))) {
            $result[] = $line;
            $i++;

            if ($i < $count && rtrim($lines[$i]) === '    {') {
                $result[] = $lines[$i];
                $i++;

                // Collect method body lines until depth=0
                $methodLines = [];
                $depth = 1;

                while ($i < $count && $depth > 0) {
                    $l = $lines[$i];
                    $stripped = rtrim($l);

                    // Brace counting (simplified)
                    $inSQ = false; $inDQ = false; $delta = 0;
                    for ($j = 0; $j < strlen($stripped); $j++) {
                        $c = $stripped[$j];
                        if ($c === "'" && !$inDQ) $inSQ = !$inSQ;
                        if ($c === '"' && !$inSQ) $inDQ = !$inDQ;
                        if (!$inSQ && !$inDQ) {
                            if ($c === '{') $delta++;
                            if ($c === '}') $delta--;
                        }
                    }
                    $depth += $delta;

                    if ($depth === 0) {
                        // Process collected method body: inject hasTable guards
                        $processed = [];
                        foreach ($methodLines as $idx => $ml) {
                            if (preg_match('/^(\s+)Schema::table\(\'([^\']+)\',/', $ml, $tm)) {
                                $tblName = $tm[2];
                                $tblIndent = $tm[1];
                                // Check if preceding lines already have hasTable for this table
                                $hasGuard = false;
                                for ($k = max(0, $idx - 6); $k < $idx; $k++) {
                                    if (strpos($methodLines[$k], "hasTable('{$tblName}')") !== false) {
                                        $hasGuard = true;
                                        break;
                                    }
                                }
                                if (!$hasGuard) {
                                    $processed[] = "{$tblIndent}if (!Schema::hasTable('{$tblName}')) { Schema::enableForeignKeyConstraints(); return; }\n";
                                    $modified = true;
                                }
                            }
                            $processed[] = $ml;
                        }
                        foreach ($processed as $pl) $result[] = $pl;
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

    $new = implode('', $result);
    if ($modified && $new !== $original) {
        file_put_contents($file, $new);
        $fixed++;
        echo "Fixed: " . basename($file) . "\n";
    }
}

echo "Total: $fixed files fixed\n";
