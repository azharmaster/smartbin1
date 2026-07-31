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

function isForcedCliRun(): bool
{
    if (PHP_SAPI !== 'cli') {
        return false;
    }

    global $argv;

    return in_array('--force', $argv ?? [], true);
}

$timezone = 'Asia/Kuala_Lumpur';
$now = Carbon::now($timezone);
$isForced = isForcedCliRun();

// The cron may call this script every two minutes. Only the first call from
// 10:00 PM onwards on the final day of the month is allowed to send.
if (!$isForced && (!$now->isLastOfMonth() || $now->hour < 22)) {
    $message = 'Skipped: monthly summary is only sent after 10:00 PM on the last day of the month.';
    writeCronLog($logFile, $message);
    renderResponse($message, 200);
    exit(0);
}

$stateDirectory = __DIR__ . '/../storage/app/cron';

if (!is_dir($stateDirectory) && !mkdir($stateDirectory, 0775, true) && !is_dir($stateDirectory)) {
    $message = 'Unable to create monthly summary state directory.';
    writeCronLog($logFile, $message);
    renderResponse($message, 500);
    exit(1);
}

$runMonth = resolveRequestedMonth();
$lockPath = $stateDirectory . '/monthly-summary-' . $runMonth . '.lock';
$statePath = $stateDirectory . '/monthly-summary-' . $runMonth . '.json';
$lockHandle = fopen($lockPath, 'c+');

if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    $message = 'Skipped: another monthly summary process is already running.';
    writeCronLog($logFile, $message);
    renderResponse($message, 200);
    exit(0);
}

$state = ['sent' => [], 'completed_at' => null];

if (is_file($statePath)) {
    $savedState = json_decode((string) file_get_contents($statePath), true);

    if (is_array($savedState)) {
        $state = array_merge($state, $savedState);
    }
}

if (!$isForced && !empty($state['completed_at'])) {
    $message = "Skipped: monthly summary for {$runMonth} was already completed at {$state['completed_at']}.";
    writeCronLog($logFile, $message);
    renderResponse($message, 200);
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(0);
}

writeCronLog($logFile, 'Collection trip monthly summary started via ' . (PHP_SAPI === 'cli' ? 'CLI' : 'WEB'));

try {
    $app = require __DIR__ . '/../bootstrap/app.php';

    /** @var Kernel $kernel */
    $kernel = $app->make(Kernel::class);
    $kernel->bootstrap();

    $monthInput = $runMonth;
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
    $alreadySentCount = 0;

    foreach ($users as $email) {
        if (!$isForced && in_array($email, $state['sent'], true)) {
            $alreadySentCount++;
            continue;
        }

        writeCronLog($logFile, 'Sending collection trip monthly summary to ' . $email);

        try {
            Mail::to($email)->send(
                new CollectionTripSummaryMail($reportTitle, $pdfOutput, $filename)
            );

            $successCount++;
            $state['sent'][] = $email;
            $state['sent'] = array_values(array_unique($state['sent']));
            file_put_contents($statePath, json_encode($state, JSON_PRETTY_PRINT), LOCK_EX);
            writeCronLog($logFile, 'Email sent to ' . $email . ' | SUCCESS');
        } catch (Throwable $e) {
            writeCronLog($logFile, 'Email failed for ' . $email . ': ' . $e->getMessage());
        }
    }

    $totalSent = count($state['sent']);
    $state['completed_at'] = $totalSent >= $users->count() ? $now->toDateTimeString() : null;
    file_put_contents($statePath, json_encode($state, JSON_PRETTY_PRINT), LOCK_EX);

    $message = "Collection trip monthly summary finished. Sent now: {$successCount}; "
        . "already sent: {$alreadySentCount}; total: {$totalSent}/{$users->count()}";
    writeCronLog($logFile, $message);
    renderResponse($message, 200);
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(0);
} catch (Throwable $e) {
    writeCronLog($logFile, 'Collection trip monthly summary failed: ' . $e->getMessage());
    writeCronLog($logFile, 'Failure location: ' . $e->getFile() . ':' . $e->getLine());
    renderResponse('Collection trip monthly summary job failed: ' . $e->getMessage(), 500);
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(1);
}
