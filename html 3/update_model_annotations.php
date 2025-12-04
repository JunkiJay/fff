<?php

// Script to update model files with @property annotations based on DDL files

// Configuration
$modelsDir = 'app/Models';
$ddlDir = 'stimule';

// Function to parse DDL file and extract column information
function parseDDLFile($filePath) {
    $content = file_get_contents($filePath);
    $columns = [];
    
    // Use a different approach to extract column definitions
    // First, find the create table statement
    if (preg_match('/create table\s+[^\(]+\(/i', $content, $startMatch, PREG_OFFSET_CAPTURE)) {
        $startPos = $startMatch[0][1] + strlen($startMatch[0][0]);
        
        // Find the matching closing parenthesis
        $depth = 1;
        $endPos = $startPos;
        $length = strlen($content);
        
        while ($depth > 0 && $endPos < $length) {
            $char = $content[$endPos];
            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            }
            $endPos++;
        }
        
        if ($depth === 0) {
            // Extract the table definition
            $tableDefinition = substr($content, $startPos, $endPos - $startPos - 1);
            
            // Use a more sophisticated approach to split the definition into column definitions
            // that handles commas within parentheses
            $columnDefs = [];
            $currentDef = '';
            $parenDepth = 0;
            
            for ($i = 0; $i < strlen($tableDefinition); $i++) {
                $char = $tableDefinition[$i];
                
                if ($char === '(') {
                    $parenDepth++;
                    $currentDef .= $char;
                } elseif ($char === ')') {
                    $parenDepth--;
                    $currentDef .= $char;
                } elseif ($char === ',' && $parenDepth === 0) {
                    // Only split on commas that are not within parentheses
                    $columnDefs[] = $currentDef;
                    $currentDef = '';
                } else {
                    $currentDef .= $char;
                }
            }
            
            // Add the last definition
            if (!empty($currentDef)) {
                $columnDefs[] = $currentDef;
            }
            
            foreach ($columnDefs as $def) {
                $def = trim($def);
                
                // Skip empty definitions
                if (empty($def)) {
                    continue;
                }
                
                // Skip lines that are not column definitions
                if (preg_match('/^primary key|^key|^index|^constraint|^foreign key|^unique/i', $def)) {
                    continue;
                }
                
                // Match column name and type
                if (preg_match('/^\s*`?([a-zA-Z0-9_]+)`?\s+([a-zA-Z0-9_]+(?:\s*\([^)]+\))?)/', $def, $matches)) {
                    $columnName = $matches[1];
                    $columnType = $matches[2];
                    
                    // Check if column is nullable
                    $isNullable = !preg_match('/\bnot\s+null\b/i', $def);
                    
                    $columns[$columnName] = [
                        'type' => $columnType,
                        'nullable' => $isNullable
                    ];
                }
            }
        }
    }
    
    return $columns;
}

// Function to map SQL type to PHP type
function mapSQLTypeToPHPType($sqlType) {
    $sqlType = strtolower($sqlType);
    
    if (strpos($sqlType, 'int') !== false || strpos($sqlType, 'bigint') !== false) {
        return 'int';
    } elseif (strpos($sqlType, 'varchar') !== false || strpos($sqlType, 'text') !== false || strpos($sqlType, 'char') !== false) {
        return 'string';
    } elseif (strpos($sqlType, 'double') !== false || strpos($sqlType, 'float') !== false || strpos($sqlType, 'decimal') !== false) {
        return 'float';
    } elseif (strpos($sqlType, 'tinyint(1)') !== false) {
        return 'bool';
    } elseif (strpos($sqlType, 'timestamp') !== false || strpos($sqlType, 'datetime') !== false || strpos($sqlType, 'date') !== false) {
        return 'string';
    } else {
        return 'mixed'; // Default type for unknown SQL types
    }
}

// Function to generate @property annotations
function generatePropertyAnnotations($columns) {
    $annotations = [];
    
    foreach ($columns as $name => $info) {
        $phpType = mapSQLTypeToPHPType($info['type']);
        $nullableStr = $info['nullable'] ? '|null' : '';
        
        $annotations[] = " * @property {$phpType}{$nullableStr} \${$name}";
    }
    
    return $annotations;
}

// Function to update model file with @property annotations
function updateModelFile($modelPath, $annotations) {
    $content = file_get_contents($modelPath);
    
    // Check if the model already has a docblock
    if (preg_match('/\/\*\*(.*?)\*\//s', $content, $matches)) {
        // Replace existing docblock
        $newDocblock = "/**\n" . implode("\n", $annotations) . "\n */";
        $content = str_replace($matches[0], $newDocblock, $content);
    } else {
        // Add new docblock before class definition
        $pattern = '/(class\s+\w+\s+(?:extends\s+\w+\s+)?(?:implements\s+\w+\s+)?)/';
        $replacement = "/**\n" . implode("\n", $annotations) . "\n */\n$1";
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    file_put_contents($modelPath, $content);
    return true;
}

// Main function to process all models
function processModels($modelsDir, $ddlDir) {
    // Get all model files
    $modelFiles = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelsDir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $modelFiles[] = $file->getPathname();
        }
    }
    
    // Get all DDL files
    $ddlFiles = glob($ddlDir . '/*.sql');
    
    // Process each model
    foreach ($modelFiles as $modelPath) {
        // Extract model name from path
        $modelName = pathinfo($modelPath, PATHINFO_FILENAME);
        
        // Find corresponding DDL file
        $ddlPath = null;
        foreach ($ddlFiles as $ddlFile) {
            $tableName = pathinfo($ddlFile, PATHINFO_FILENAME);
            
            // Handle special case for Slots model in Slots directory
            if ($modelName === 'Slots' && dirname($modelPath) === $modelsDir . '/Slots') {
                if ($tableName === 'slots') {
                    $ddlPath = $ddlFile;
                    break;
                }
            }
            // Convert model name to snake_case for table name comparison
            elseif (strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName)) === $tableName) {
                $ddlPath = $ddlFile;
                break;
            }
            // Try plural form of the model name
            elseif (strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName)) . 's' === $tableName) {
                $ddlPath = $ddlFile;
                break;
            }
        }
        
        if ($ddlPath) {
            echo "Processing model: $modelName with DDL: " . basename($ddlPath) . PHP_EOL;
            
            // Parse DDL file
            $columns = parseDDLFile($ddlPath);
            
            // Generate annotations
            $annotations = generatePropertyAnnotations($columns);
            
            // Update model file
            if (updateModelFile($modelPath, $annotations)) {
                echo "Updated model: $modelName" . PHP_EOL;
            } else {
                echo "Failed to update model: $modelName" . PHP_EOL;
            }
        } else {
            echo "No matching DDL file found for model: $modelName" . PHP_EOL;
        }
    }
}

// Run the script to process all models
processModels($modelsDir, $ddlDir);

echo "Done!" . PHP_EOL;