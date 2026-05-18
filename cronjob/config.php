<?php

$env = [];
$envPath = dirname(__DIR__) . '/.env';

if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }

        $env[$key] = $value;
    }
}

return [
    'db' => [
        'host' => $env['DB_HOST'] ?? 'localhost',
        'name' => $env['DB_DATABASE'] ?? 'u311595433_smartbin1',
        'user' => $env['DB_USERNAME'] ?? 'u311595433_smartbin1',
        'pass' => $env['DB_PASSWORD'] ?? '3b3^Msah:=$R',
        'charset' => 'utf8mb4',
    ],

    'work_hours' => [
        'start' => '07:00',
        'end'   => '22:00',
    ],
];
