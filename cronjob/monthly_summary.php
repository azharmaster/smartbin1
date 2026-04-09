<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

date_default_timezone_set('Asia/Kuala_Lumpur');

$logFile = __DIR__ . '/cron.log';
$isCli = PHP_SAPI === 'cli';

function writeCronLog(string $logFile, string $message): void
{
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | {$message}\n", FILE_APPEND);
}

function renderResponse(string $message, int $statusCode = 200): void
{
    if (PHP_SAPI !== 'cli') {
        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=utf-8');
    }

    echo $message;
}

writeCronLog($logFile, 'Monthly summary cron started via ' . ($isCli ? 'CLI' : 'WEB'));

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

    $response = $output !== '' ? $output : 'Monthly summary job completed.';
    renderResponse($response, $exitCode === 0 ? 200 : 500);

    exit($exitCode);
} catch (Throwable $e) {
    writeCronLog($logFile, 'Monthly summary cron failed: ' . $e->getMessage());
    writeCronLog($logFile, 'Failure location: ' . $e->getFile() . ':' . $e->getLine());
    renderResponse('Monthly summary job failed: ' . $e->getMessage(), 500);
    exit(1);
}
