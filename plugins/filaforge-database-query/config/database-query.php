<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Query Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database query execution and security
    |
    */
    
    'security' => [
        'allowed_tables' => env('DB_QUERY_ALLOWED_TABLES', '*'),
        'blocked_keywords' => [
            'DROP', 'TRUNCATE', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE',
            'GRANT', 'REVOKE', 'EXECUTE', 'EXEC'
        ],
        'max_execution_time' => env('DB_QUERY_MAX_TIME', 30), // seconds
        'max_results' => env('DB_QUERY_MAX_RESULTS', 1000),
    ],
    
    'features' => [
        'save_queries' => env('DB_QUERY_SAVE_ENABLED', true),
        'query_history' => env('DB_QUERY_HISTORY_ENABLED', true),
        'export_results' => env('DB_QUERY_EXPORT_ENABLED', true),
        'auto_complete' => env('DB_QUERY_AUTOCOMPLETE', true),
    ],
    
    'permissions' => [
        'execute_queries' => 'execute-database-queries',
        'save_queries' => 'save-database-queries',
        'view_history' => 'view-query-history',
        'export_results' => 'export-query-results',
    ],
];
