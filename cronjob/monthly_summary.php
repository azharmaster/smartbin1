<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Controllers\SummaryController;
use App\Mail\SummaryReportMail;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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

function buildReportTitle(Carbon $baseDate, string $period): string
{
    if ($period === 'today') {
        return 'Daily Report - ' . $baseDate->format('d M Y');
    }

    if ($period === 'week') {
        $start = $baseDate->copy()->startOfWeek();
        $end = $baseDate->copy()->endOfWeek();
        $weekNumber = $baseDate->weekOfYear;

        return 'Weekly Report - Week ' . $weekNumber
            . ' (' . $start->format('d M Y')
            . ' - ' . $end->format('d M Y') . ')';
    }

    $start = $baseDate->copy()->startOfMonth();
    $end = $baseDate->copy()->endOfMonth();

    return 'Monthly Report - '
        . $baseDate->format('F Y')
        . ' (' . $start->format('d M')
        . ' - ' . $end->format('d M Y') . ')';
}

function fetchChartDataUrl($binAnalytics, string $label, $data, string $border, string $bg): string
{
    $labels = $binAnalytics->pluck('asset_name')->values();

    $url = "https://quickchart.io/chart?c=" . urlencode(json_encode([
        'type' => 'bar',
        'data' => [
            'labels' => $labels,
            'datasets' => [[
                'label' => $label,
                'data' => $data,
                'borderColor' => $border,
                'backgroundColor' => $bg,
                'fill' => true,
                'tension' => 0.3,
            ]],
        ],
    ]));

    return 'data:image/png;base64,' . base64_encode(file_get_contents($url));
}

writeCronLog($logFile, 'Monthly summary started via ' . (PHP_SAPI === 'cli' ? 'CLI' : 'WEB'));

try {
    $app = require __DIR__ . '/../bootstrap/app.php';

    /** @var Kernel $kernel */
    $kernel = $app->make(Kernel::class);
    $kernel->bootstrap();

    $period = 'month';
    $monthInput = resolveRequestedMonth();
    $baseDate = Carbon::parse($monthInput . '-01', 'Asia/Kuala_Lumpur');
    $reportTitle = buildReportTitle($baseDate, $period);

    /** @var SummaryController $summaryController */
    $summaryController = $app->make(SummaryController::class);

    $capacityStats = $summaryController->getCapacityStats($baseDate, $period);
    $devicesByFloor = $summaryController->getDevicesByFloor();
    $binAnalytics = $summaryController->computeBinAnalyticsPerAsset($baseDate, $period);
    $assets = $summaryController->getAssetsPublic();
    $cleaningLogs = $summaryController->getCleaningLogs($baseDate, $period);
    $summaryMetrics = $summaryController->computeSummaryMetrics($baseDate, $period);
    $monthInsights = $summaryController->computeMonthInsights($baseDate, $period);

    $timesFullChartData = fetchChartDataUrl(
        $binAnalytics,
        'Times Became Full',
        $binAnalytics->pluck('times_full')->values(),
        '#8e44ad',
        'rgba(142,68,173,0.8)'
    );

    $avgFillChartData = fetchChartDataUrl(
        $binAnalytics,
        'Average Fill Time (Hours)',
        $binAnalytics->pluck('avg_fill_time')->values(),
        '#2ecc71',
        'rgba(46,204,113,0.8)'
    );

    $avgClearChartData = fetchChartDataUrl(
        $binAnalytics,
        'Average Clear Time (Hours)',
        $binAnalytics->pluck('avg_clear_time')->values(),
        '#e74c3c',
        'rgba(231,76,60,0.8)'
    );

    $pdf = Pdf::loadView('emails.summary_report', [
        'reportTitle' => $reportTitle,
        'period' => $period,
        'baseDate' => $baseDate,
        'capacityStats' => $capacityStats,
        'devicesByFloor' => $devicesByFloor,
        'binAnalytics' => $binAnalytics,
        'assets' => $assets,
        'cleaningLogs' => $cleaningLogs,
        'monthInput' => $monthInput,
        'summaryMetrics' => $summaryMetrics,
        'monthInsights' => $monthInsights,
        'timesFullChartData' => $timesFullChartData,
        'avgFillChartData' => $avgFillChartData,
        'avgClearChartData' => $avgClearChartData,
    ])->setPaper('a4', 'portrait');

    $users = User::where('id', 6)
        ->whereNotNull('email')
        ->get();

    if ($users->count() === 0) {
        writeCronLog($logFile, 'User id=6 with email was not found.');
        renderResponse('User id=6 with email was not found.', 404);
        exit(1);
    }

    $successCount = 0;

    foreach ($users as $user) {
        writeCronLog($logFile, 'Sending monthly summary to ' . $user->email);

        Mail::to($user->email)->send(
            new SummaryReportMail([
                'reportTitle' => $reportTitle,
            ], $pdf->output())
        );

        $successCount++;
        writeCronLog($logFile, 'Email sent to ' . $user->email . ' | SUCCESS');
    }

    $message = "Monthly summary finished. Success: {$successCount}/{$users->count()}";
    writeCronLog($logFile, $message);
    renderResponse($message, 200);
    exit(0);
} catch (Throwable $e) {
    writeCronLog($logFile, 'Monthly summary failed: ' . $e->getMessage());
    writeCronLog($logFile, 'Failure location: ' . $e->getFile() . ':' . $e->getLine());
    renderResponse('Monthly summary job failed: ' . $e->getMessage(), 500);
    exit(1);
}
