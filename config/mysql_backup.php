<?php

return [
    'enabled' => (bool) env('MYSQL_BACKUP_ENABLED', false) && env('APP_ENV', 'production') !== 'production',
    'automatic' => (bool) env('MYSQL_BACKUP_AUTOMATIC', false),
    'time' => env('MYSQL_BACKUP_TIME', '02:00'),
    'timezone' => env('MYSQL_BACKUP_TIMEZONE', 'Asia/Ho_Chi_Minh'),
    'retention_days' => (int) env('MYSQL_BACKUP_RETENTION_DAYS', 30),
    'operator_ids' => array_values(array_filter(array_map('trim', explode(',', (string) env('MYSQL_BACKUP_OPERATOR_IDS', ''))))),
    'root' => storage_path('app/private/mysql-backups'),
    'timeout' => (int) env('MYSQL_BACKUP_TIMEOUT', 300),
    'dump_client' => env('MYSQL_BACKUP_DUMP_CLIENT', 'mysqldump'),
    'mysql_client' => env('MYSQL_BACKUP_MYSQL_CLIENT', 'mysql'),
    'source' => [
        'driver' => 'mysql',
        'host' => env('MYSQL_BACKUP_HOST', '127.0.0.1'),
        'port' => env('MYSQL_BACKUP_PORT', '3306'),
        'schema' => env('MYSQL_BACKUP_SCHEMA'),
        'user' => env('MYSQL_BACKUP_USER'),
        'password' => env('MYSQL_BACKUP_PASSWORD'),
    ],
    'restore' => [
        'driver' => 'mysql',
        'host' => env('MYSQL_RESTORE_HOST', '127.0.0.1'),
        'port' => env('MYSQL_RESTORE_PORT', '3306'),
        'schema' => env('MYSQL_RESTORE_SCHEMA'),
        'user' => env('MYSQL_RESTORE_USER'),
        'password' => env('MYSQL_RESTORE_PASSWORD'),
    ],
];

