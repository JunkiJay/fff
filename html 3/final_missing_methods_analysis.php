<?php

// Final comprehensive analysis for missing methods
$projectRoot = __DIR__;
$missingMethods = [];

echo "=== COMPREHENSIVE ANALYSIS OF MISSING METHODS ===\n\n";

// Known missing methods from manual verification
$knownMissing = [
    [
        'method' => 'SlotServiceFacade::getImageByName',
        'description' => 'Method getImageByName does not exist in SlotsService class (only getImage exists)',
        'files' => [
            'app/Console/Commands/Slots/SyncSlotsCommand.php:33',
            'app/Console/Commands/Slots/SyncSlotsCommand.php:51'
        ]
    ]
];

// Let's also manually search for some other potential issues
function searchForPotentialMissingMethods() {
    global $projectRoot;
    
    $issues = [];
    
    // Search for calls to methods that might be typos or missing
    $files = glob($projectRoot . '/app/**/*.php', GLOB_BRACE);
    $files = array_merge($files, glob($projectRoot . '/app/*/*.php'));
    $files = array_merge($files, glob($projectRoot . '/app/*/*/*.php'));
    $files = array_merge($files, glob($projectRoot . '/app/*/*/*/*.php'));
    $files = array_merge($files, glob($projectRoot . '/app/*/*/*/*/*.php'));
    
    foreach ($files as $file) {
        if (!file_exists($file)) continue;
        
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNum => $line) {
            // Look for specific suspicious patterns
            
            // 1. Check for undefined static method calls on custom classes
            if (preg_match('/([A-Z][A-Za-z0-9]+)::([a-z][A-Za-z0-9_]+)\s*\(/', $line, $matches)) {
                $className = $matches[1];
                $methodName = $matches[2];
                
                // Skip Laravel/known classes
                $skipClasses = ['DB', 'Log', 'Auth', 'Route', 'Redis', 'Cache', 'Hash', 'Carbon', 'Str', 'Arr', 'URL', 'Rule', 'Broadcast'];
                if (in_array($className, $skipClasses)) {
                    continue;
                }
                
                // Check if class exists in app directory
                $classPattern = "class $className";
                $classFound = false;
                
                exec("grep -r \"$classPattern\" app/ 2>/dev/null", $output);
                if (!empty($output)) {
                    $classFound = true;
                }
                
                if ($classFound) {
                    // Check if method exists in the class
                    $methodPattern = "function $methodName";
                    exec("grep -r \"$methodPattern\" app/ 2>/dev/null", $methodOutput);
                    
                    $methodFound = false;
                    foreach ($methodOutput as $methodLine) {
                        if (strpos($methodLine, $className) !== false || strpos($methodLine, strtolower($className)) !== false) {
                            $methodFound = true;
                            break;
                        }
                    }
                    
                    if (!$methodFound) {
                        $issues[] = [
                            'method' => "$className::$methodName",
                            'file' => str_replace($projectRoot . '/', '', $file),
                            'line' => $lineNum + 1,
                            'context' => trim($line),
                            'type' => 'missing_static_method'
                        ];
                    }
                }
            }
            
            // 2. Look for calls to obviously custom methods that might be missing
            if (preg_match('/\$this->([a-z][A-Za-z0-9_]{5,})\s*\(/', $line, $matches)) {
                $methodName = $matches[1];
                
                // Skip common patterns
                if (preg_match('/(validate|authorize|middleware|dispatch|increment|decrement)/', $methodName)) {
                    continue;
                }
                
                $issues[] = [
                    'method' => '$this->' . $methodName,
                    'file' => str_replace($projectRoot . '/', '', $file),
                    'line' => $lineNum + 1,
                    'context' => trim($line),
                    'type' => 'potential_missing_instance_method'
                ];
            }
        }
    }
    
    return $issues;
}

// Display known missing methods
echo "CONFIRMED MISSING METHODS:\n";
foreach ($knownMissing as $issue) {
    echo "• {$issue['method']}\n";
    echo "  Description: {$issue['description']}\n";
    echo "  Called in:\n";
    foreach ($issue['files'] as $fileRef) {
        echo "    - $fileRef\n";
    }
    echo "\n";
}

// Search for additional potential issues
echo "ADDITIONAL SUSPICIOUS METHOD CALLS:\n";
$suspiciousIssues = searchForPotentialMissingMethods();

// Group and display results
$grouped = [];
foreach ($suspiciousIssues as $issue) {
    $key = $issue['method'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [];
    }
    $grouped[$key][] = $issue;
}

$count = 0;
foreach ($grouped as $method => $calls) {
    if (count($calls) >= 2 && $count < 10) { // Show top 10 most suspicious
        echo "• $method (called " . count($calls) . " times)\n";
        foreach (array_slice($calls, 0, 3) as $call) { // Show first 3 occurrences
            echo "    - {$call['file']}:{$call['line']} | {$call['context']}\n";
        }
        echo "\n";
        $count++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "This analysis focused on finding genuinely missing custom application methods.\n";
echo "Most framework methods (Laravel, Eloquent, etc.) were excluded from results.\n";
echo "Manual verification is recommended for the suspicious cases listed above.\n";