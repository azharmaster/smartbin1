<?php

$config = require __DIR__ . '/config.php';

try {
    $db = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    file_put_contents(__DIR__.'/cron.log', date('Y-m-d H:i')." DB ERROR: ".$e->getMessage()."\n", FILE_APPEND);
    exit;
}