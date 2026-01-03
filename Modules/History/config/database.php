<?php

return [
    /*
    |--------------------------------------------------------------------------
    | History Module Database Connection
    |--------------------------------------------------------------------------
    |
    | This option controls the database connection used by the History module.
    | You can override this in your .env file using MODULE_HISTORY_DB_CONNECTION
    |
    */
    'connection' => env('MODULE_HISTORY_DB_CONNECTION', 'pgsql_history'),
];
