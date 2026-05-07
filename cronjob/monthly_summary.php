<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Controllers\CollectionTripController;
use App\Mail\CollectionTripSummaryMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Mail;

date_default_timezone_set('Asia/Kuala_Lumpur');

$logFile = __DIR__ . '/cron.log';

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

function resolveRequestedMonth(): string
{
    if (PHP_SAPI === 'cli') {
        global $argv;

        if (!empty($argv[1]) && preg_match('/^\d{4}-\d{2}$/', (string) $argv[1])) {
            return (string) $argv[1];
        }
    }

    $month = $_GET['month'] ?? '';

    if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month)) {
        return $month;
    }

    return Carbon::now('Asia/Kuala_Lumpur')->format('Y-m');
}

function buildReportTitle(string $rangeLabel): string
{
    return 'Collection Trip Summary - ' . $rangeLabel;
}

writeCronLog($logFile, 'Collection trip monthly summary started via ' . (PHP_SAPI === 'cli' ? 'CLI' : 'WEB'));

try {
    $app = require __DIR__ . '/../bootstrap/app.php';

    /** @var Kernel $kernel */
    $kernel = $app->make(Kernel::class);
    $kernel->bootstrap();

    $monthInput = resolveRequestedMonth();
    $baseDate = Carbon::parse($monthInput . '-01', 'Asia/Kuala_Lumpur');

    $filters = [
        'period' => 'monthly',
        'month' => $baseDate->format('Y-m'),
        'capacity_filter' => 'empty',
    ];

    /** @var CollectionTripController $collectionTripController */
    $collectionTripController = $app->make(CollectionTripController::class);

    $summaryData = $collectionTripController->getSummaryViewData($filters);
    $pdfOutput = $collectionTripController->generateSummaryPdf($filters);
    $reportTitle = buildReportTitle($summaryData['rangeLabel']);
    $filename = 'collection-trip-summary-' . str($summaryData['rangeLabel'])->slug() . '.pdf';

    $users = User::query()
        ->where('role', 1)
        ->whereNotNull('email')
        ->pluck('email')
        ->filter()
        ->values();

    if ($users->count() === 0) {
        writeCronLog($logFile, 'No admin users with email found.');
        renderResponse('No admin users with email found.', 404);
        exit(1);
    }

    $successCount = 0;

    foreach ($users as $email) {
        writeCronLog($logFile, 'Sending collection trip monthly summary to ' . $email);

        Mail::to($email)->send(
            new CollectionTripSummaryMail($reportTitle, $pdfOutput, $filename)
        );

        $successCount++;
        writeCronLog($logFile, 'Email sent to ' . $email . ' | SUCCESS');
    }

    $message = "Collection trip monthly summary finished. Success: {$successCount}/{$users->count()}";
    writeCronLog($logFile, $message);
    renderResponse($message, 200);
    exit(0);
} catch (Throwable $e) {
    writeCronLog($logFile, 'Collection trip monthly summary failed: ' . $e->getMessage());
    writeCronLog($logFile, 'Failure location: ' . $e->getFile() . ':' . $e->getLine());
    renderResponse('Collection trip monthly summary job failed: ' . $e->getMessage(), 500);
    exit(1);
}
