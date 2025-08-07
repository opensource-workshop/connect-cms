<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Plan Limit (MB)
    |--------------------------------------------------------------------------
    |
    | Set the storage limit for the service plan (in MB).
    | Leave empty to disable plan capacity display.
    | This value will be converted to bytes internally.
    |
    */

    'limit_mb' => env('STORAGE_LIMIT_MB'),

    /*
    |--------------------------------------------------------------------------
    | Storage Usage Warning Threshold
    |--------------------------------------------------------------------------
    |
    | Set the threshold for displaying warning messages when storage usage
    | reaches this percentage. Value should be between 0.0 and 1.0.
    | Default is 0.8 (80%).
    |
    */

    'warning_threshold' => env('STORAGE_USAGE_WARNING_THRESHOLD', 0.8),

];