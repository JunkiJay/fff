<?php

class MethodCallAnalyzer
{
    private $projectRoot;
    private $phpFiles = [];
    private $classDefinitions = [];
    private $methodDefinitions = [];
    private $methodCalls = [];
    private $missingMethods = [];

    public function __construct($projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    public function analyze()
    {
        echo "Analyzing project for missing method calls...\n";
        
        // Get all PHP files in app directory (excluding vendor)
        $this->findPhpFiles();
        echo "Found " . count($this->phpFiles) . " PHP files to analyze\n";
        
        // Parse all files to find class and method definitions
        $this->findDefinitions();
        echo "Found " . count($this->classDefinitions) . " classes and " . count($this->methodDefinitions) . " methods\n";
        
        // Find all method calls
        $this->findMethodCalls();
        echo "Found " . count($this->methodCalls) . " method calls\n";
        
        // Check which method calls don't have corresponding definitions
        $this->findMissingMethods();
        
        // Report results
        $this->reportResults();
    }

    private function findPhpFiles()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot . '/app')
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->phpFiles[] = $file->getPathname();
            }
        }

        // Also include some root files
        $rootFiles = glob($this->projectRoot . '/*.php');
        $this->phpFiles = array_merge($this->phpFiles, $rootFiles);
    }

    private function findDefinitions()
    {
        foreach ($this->phpFiles as $file) {
            $content = file_get_contents($file);
            
            // Find class definitions
            if (preg_match_all('/(?:class|interface|trait)\s+(\w+)/i', $content, $matches)) {
                foreach ($matches[1] as $className) {
                    $this->classDefinitions[$className] = $file;
                }
            }
            
            // Find method definitions
            if (preg_match_all('/(?:public|private|protected|static)?\s*function\s+(\w+)\s*\(/i', $content, $matches)) {
                foreach ($matches[1] as $methodName) {
                    if (!isset($this->methodDefinitions[$methodName])) {
                        $this->methodDefinitions[$methodName] = [];
                    }
                    $this->methodDefinitions[$methodName][] = $file;
                }
            }
        }
    }

    private function findMethodCalls()
    {
        foreach ($this->phpFiles as $file) {
            $content = file_get_contents($file);
            $lines = explode("\n", $content);
            
            foreach ($lines as $lineNum => $line) {
                // Find method calls like $obj->method(), $this->method(), static::method(), Class::method()
                if (preg_match_all('/(?:\$\w+->|::|->)(\w+)\s*\(/i', $line, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[1] as $match) {
                        $methodName = $match[0];
                        
                        // Skip common PHP functions and Laravel helpers
                        if ($this->isCommonFunction($methodName)) {
                            continue;
                        }
                        
                        $this->methodCalls[] = [
                            'method' => $methodName,
                            'file' => $file,
                            'line' => $lineNum + 1,
                            'context' => trim($line)
                        ];
                    }
                }
            }
        }
    }

    private function isCommonFunction($methodName)
    {
        $commonFunctions = [
            // Basic PHP functions
            'get', 'set', 'has', 'empty', 'isset', 'unset', 'array_merge', 'in_array',
            'json_encode', 'json_decode', 'substr', 'strlen', 'trim', 'explode', 'implode',
            'count', 'sort', 'filter', 'map', 'each', 'push', 'pop', 'shift', 'unshift',
            'getPathname', 'getExtension', 'isFile', 'isDir',
            
            // Laravel Eloquent methods
            'all', 'first', 'find', 'where', 'create', 'update', 'delete', 'save', 'query',
            'toArray', 'toJson', 'latest', 'oldest', 'orderBy', 'groupBy', 'select', 'with',
            'withCount', 'without', 'whereBetween', 'whereIn', 'whereNotIn', 'orWhere',
            'paginate', 'simplePaginate', 'chunk', 'pluck', 'sum', 'avg', 'min', 'max',
            'increment', 'decrement', 'exists', 'doesntExist', 'when', 'unless', 'tap',
            'inRandomOrder', 'limit', 'take', 'skip', 'offset', 'distinct', 'join',
            'leftJoin', 'rightJoin', 'union', 'having', 'raw', 'fromSub', 'lock',
            'startOfDay', 'endOfDay', 'startOfWeek', 'endOfWeek', 'startOfMonth', 'endOfMonth',
            
            // Carbon methods
            'parse', 'now', 'today', 'yesterday', 'tomorrow', 'subDay', 'subWeek', 'subMonth',
            'addDay', 'addWeek', 'addMonth', 'format', 'diffInDays', 'diffInHours',
            
            // Laravel helpers and facades
            'route', 'redirect', 'response', 'view', 'abort', 'back', 'url', 'asset', 'config',
            'env', 'auth', 'session', 'request', 'cache', 'log', 'trans', '__', 'collect',
            'dd', 'dump', 'bcrypt', 'hash', 'encrypt', 'decrypt', 'old', 'csrf_token',
            'validator', 'factory', 'make', 'resolve', 'app', 'storage_path', 'public_path',
            
            // HTTP and validation
            'validate', 'fails', 'errors', 'rules', 'messages', 'attributes', 'sometimes',
            'json', 'header', 'cookie', 'input', 'only', 'except', 'merge', 'replace',
            
            // Collection methods
            'each', 'map', 'filter', 'reject', 'reduce', 'flatten', 'unique', 'values',
            'keys', 'flip', 'reverse', 'chunk', 'split', 'partition', 'groupBy', 'keyBy',
            'sortBy', 'sortByDesc', 'shuffle', 'random', 'slice', 'splice', 'forPage',
            
            // String methods
            'upper', 'lower', 'title', 'studly', 'camel', 'snake', 'kebab', 'slug',
            'plural', 'singular', 'ucfirst', 'lcfirst', 'replace', 'replaceFirst',
            'replaceLast', 'replaceArray', 'replaceMatches', 'start', 'finish', 'before',
            'after', 'beforeLast', 'afterLast', 'between', 'contains', 'startsWith',
            'endsWith', 'is', 'isUuid', 'match', 'matchAll', 'test', 'split', 'limit',
            
            // Common method names that might be confused
            'run', 'execute', 'handle', 'call', 'invoke', 'dispatch', 'fire', 'trigger',
            'boot', 'register', 'bind', 'singleton', 'instance', 'extend', 'macro',
            'mixin', 'proxy', 'forget', 'flush', 'clear', 'reset', 'refresh', 'reload'
        ];
        
        return in_array(strtolower($methodName), $commonFunctions);
    }

    private function findMissingMethods()
    {
        foreach ($this->methodCalls as $call) {
            $methodName = $call['method'];
            
            // Check if method exists in our definitions
            if (!isset($this->methodDefinitions[$methodName])) {
                $this->missingMethods[] = $call;
            }
        }
    }

    private function reportResults()
    {
        if (empty($this->missingMethods)) {
            echo "No missing method calls found!\n";
            return;
        }

        echo "\n=== MISSING METHOD CALLS FOUND ===\n";
        echo "Total missing method calls: " . count($this->missingMethods) . "\n\n";

        // Group by method name
        $groupedMissing = [];
        foreach ($this->missingMethods as $missing) {
            $methodName = $missing['method'];
            if (!isset($groupedMissing[$methodName])) {
                $groupedMissing[$methodName] = [];
            }
            $groupedMissing[$methodName][] = $missing;
        }

        foreach ($groupedMissing as $methodName => $calls) {
            echo "METHOD: $methodName (called " . count($calls) . " times)\n";
            foreach ($calls as $call) {
                $relativePath = str_replace($this->projectRoot . '/', '', $call['file']);
                echo "  - {$relativePath}:{$call['line']} | {$call['context']}\n";
            }
            echo "\n";
        }
    }
}

// Run the analysis
$analyzer = new MethodCallAnalyzer(__DIR__);
$analyzer->analyze();