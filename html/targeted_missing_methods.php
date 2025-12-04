<?php

// Targeted analysis for genuinely missing methods
// Focus on custom application methods, not framework methods

$projectRoot = __DIR__;
$missingMethods = [];

// Function to get all PHP files in app directory
function getAppPhpFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

// Get all PHP files
$phpFiles = getAppPhpFiles($projectRoot . '/app');

echo "Analyzing " . count($phpFiles) . " files for genuinely missing methods...\n\n";

// Look for specific suspicious patterns
$suspiciousPatterns = [
    // Look for method calls that might be typos or renamed methods
    '/\$this->([A-Za-z][A-Za-z0-9_]*)\s*\(/',
    '/([A-Z][A-Za-z0-9_]*)::\s*([a-z][A-Za-z0-9_]*)\s*\(/',
    '/\$[a-z][A-Za-z0-9_]*->([a-z][A-Za-z0-9_]*)\s*\(/'
];

$potentialIssues = [];

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    
    foreach ($lines as $lineNum => $line) {
        // Skip comments and strings
        if (preg_match('/^\s*(\/\/|\/\*|\*|#)/', $line)) {
            continue;
        }
        
        // Look for specific suspicious method calls
        if (preg_match('/\$this->([a-z][A-Za-z0-9_]*)\s*\(/', $line, $matches)) {
            $methodName = $matches[1];
            
            // Skip obvious framework methods
            if (in_array($methodName, ['validate', 'authorize', 'middleware', 'user', 'guard'])) {
                continue;
            }
            
            // Look for methods that might be missing
            if (preg_match('/[A-Z]/', $methodName) || strlen($methodName) > 15) {
                $potentialIssues[] = [
                    'method' => $methodName,
                    'file' => str_replace($projectRoot . '/', '', $file),
                    'line' => $lineNum + 1,
                    'context' => trim($line),
                    'type' => 'this_call'
                ];
            }
        }
        
        // Look for static calls to custom classes
        if (preg_match('/([A-Z][A-Za-z0-9_]+)::\s*([a-z][A-Za-z0-9_]+)\s*\(/', $line, $matches)) {
            $className = $matches[1];
            $methodName = $matches[2];
            
            // Skip known Laravel classes
            $laravelClasses = ['DB', 'Log', 'Auth', 'Route', 'Redis', 'Cache', 'Hash', 'Carbon', 'Str', 'Arr'];
            if (in_array($className, $laravelClasses)) {
                continue;
            }
            
            $potentialIssues[] = [
                'method' => $className . '::' . $methodName,
                'file' => str_replace($projectRoot . '/', '', $file),
                'line' => $lineNum + 1,
                'context' => trim($line),
                'type' => 'static_call'
            ];
        }
    }
}

// Manual checks for specific known issues
echo "=== MANUAL VERIFICATION OF SUSPICIOUS METHODS ===\n\n";

// Group by method name
$grouped = [];
foreach ($potentialIssues as $issue) {
    $key = $issue['method'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [];
    }
    $grouped[$key][] = $issue;
}

foreach ($grouped as $method => $calls) {
    if (count($calls) >= 2) { // Only show methods called multiple times
        echo "METHOD: $method (called " . count($calls) . " times)\n";
        foreach ($calls as $call) {
            echo "  - {$call['file']}:{$call['line']} | {$call['context']}\n";
        }
        echo "\n";
    }
}