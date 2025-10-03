<?php

namespace Core;

use Doctrine\DBAL\Logging\SQLLogger;

class QueryLogger implements SQLLogger
{
    public array $queries = [];
    private ?float $startTime = null;

    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->startTime = microtime(true);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $caller = null;
        $projectRoot = dirname(__DIR__, 2);

        foreach ($trace as $entry) {
            if (!isset($entry['file'])) {
                continue;
            }
            
            $file = str_replace('\\', '/', $entry['file']);

            // If the file is outside our project root, skip it
            if (strpos($file, str_replace('\\', '/', $projectRoot)) === false) {
                continue;
            }

            // Skip any file inside the 'vendor' or 'core' directory
            $isVendorFile = strpos($file, '/vendor/') !== false;
            $isCoreFile = strpos($file, '/core/') !== false;

            if (!$isVendorFile && !$isCoreFile) {
                $caller = [
                    'file' => str_replace(str_replace('\\', '/', $projectRoot) . '/', '', $file),
                    'line' => $entry['line'],
                ];
                break; 
            }
        }
        
        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'executionMS' => 0,
            'caller' => $caller,
        ];
    }

    public function stopQuery(): void
    {
        $lastQueryKey = array_key_last($this->queries);
        if ($lastQueryKey !== null) {
            $this.queries[$lastQueryKey]['executionMS'] = microtime(true) - $this->startTime;
        }
    }
}