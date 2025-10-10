<?php

namespace Core;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * A simple SQL logger that counts and times queries.
 * This is used by the Debug Toolbar.
 */
class QueryLogger implements SQLLogger
{
    public array $queries     = [];
    private float $start_time = 0;

    /**
     * @param $sql
     * @param array $params
     * @param array $types
     */
    public function startQuery( $sql, ?array $params = null, ?array $types = null )
    {
        $this->start_time = microtime( true );
        $this->queries[]  = [
            'sql'            => $sql,
            'params'         => $params,
            'types'          => $types,
            'execution_time' => 0,
        ];
    }

    public function stopQuery()
    {
        $last_query_key = array_key_last( $this->queries );
        if ( $last_query_key !== null ) {
            $this->queries[$last_query_key]['execution_time'] = microtime( true ) - $this->start_time;
        }
    }

    public function __destruct()
    {
        // This is a failsafe. If a query is started but never stopped
        // (e.g., due to an exception), we'll mark its time as 'unfinished'.
        $last_query_key = array_key_last( $this->queries );

        if ( $last_query_key !== null ) {
            // Use a direct isset() check to avoid the "temporary expression" error.
            if ( isset( $this->queries[$last_query_key]['execution_time'] ) && $this->queries[$last_query_key]['execution_time'] === 0 ) {
                $this->queries[$last_query_key]['execution_time'] = 'unfinished';
            }
        }
    }
}
