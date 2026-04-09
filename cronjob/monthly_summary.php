<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

date_default_timezone_set('Asia/Kuala_Lumpur');

$logFile = __DIR__ . '/cron.log';

function writeCronLog(string $logFile, string $message): void
{
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | {$message}\n", FILE_APPEND);
}

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'This script can only be run from CLI.';
    exit(1);
}

writeCronLog($logFile, 'Monthly summary cron started');

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';

    /** @var Kernel $kernel */
    $kernel = $app->make(Kernel::class);
    $kernel->bootstrap();

    $exitCode = $kernel->call('summary:send-monthly');
    $output = trim($kernel->output());

    writeCronLog($logFile, "summary:send-monthly exit code: {$exitCode}");

    if ($output !== '') {
        writeCronLog($logFile, "Command output:\n{$output}");
    }

    exit($exitCode);
} catch (Throwable $e) {
    writeCronLog($logFile, 'Monthly summary cron failed: ' . $e->getMessage());
    writeCronLog($logFile, 'Failure location: ' . $e->getFile() . ':' . $e->getLine());
    exit(1);
}
