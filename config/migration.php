<?php

return [

    // MIGRATION_CONFIG ファイルのパス
    'MIGRATION_CONFIG_PATH' => env('MIGRATION_CONFIG_PATH'),

    // 移行処理の標準出力への出力
    'MIGRATION_JOB_MONITOR' => env('MIGRATION_JOB_MONITOR', true),

    // 移行処理のログへの出力
    'MIGRATION_JOB_LOG' => env('MIGRATION_JOB_LOG', true),

    // NC2 のアップロードファイルのパス
    'NC2_EXPORT_UPLOADS_PATH' => env('NC2_EXPORT_UPLOADS_PATH'),

    // NC3 のアップロードファイルのパス
    'NC3_EXPORT_UPLOADS_PATH' => env('NC3_EXPORT_UPLOADS_PATH'),
];
