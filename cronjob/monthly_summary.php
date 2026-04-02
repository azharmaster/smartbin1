<?php

declare(strict_types=1);

use App\Http\Controllers\SummaryController;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$logFile = __DIR__ . '/cron.log';
$now = Carbon::now('Asia/Kuala_Lumpur');

function logMessage(string $logFile, string $message): void
{
    file_put_contents(
        $logFile,
        date('Y-m-d H:i') . " | {$message}\n",
        FILE_APPEND
    );
}

function transparentPngDataUri(): string
{
    return 'data:image/png;base64,' . base64_encode(base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wn6mXQAAAAASUVORK5CYII='
    ));
}

function buildChartDataUri(array $config, string $logFile): string
{
    $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($config));
    $binary = @file_get_contents($url);

    if ($binary === false) {
        logMessage($logFile, 'QuickChart fetch failed. Using transparent placeholder image.');
        return transparentPngDataUri();
    }

    return 'data:image/png;base64,' . base64_encode($binary);
}

function sendEmailSMTP(
    string $to,
    string $subject,
    string $body,
    string $logFile,
    ?string $attachmentPath = null,
    ?string $attachmentName = null
): bool {
    $mail = new PHPMailer(true);

    $mailHost       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    $mailPort       = (int) ($_ENV['MAIL_PORT'] ?? 465);
    $mailUsername   = $_ENV['MAIL_USERNAME'] ?? 'smartbin2026@gmail.com';
    $mailPassword   = $_ENV['MAIL_PASSWORD'] ?? '';
    $mailEncryption = $_ENV['MAIL_ENCRYPTION'] ?? 'ssl';
    $mailFrom       = $_ENV['MAIL_FROM_ADDRESS'] ?? 'smartbin2026@gmail.com';
    $mailFromName   = $_ENV['MAIL_FROM_NAME'] ?? 'SmartBin Reports';

    try {
        $mail->isSMTP();
        $mail->Host       = $mailHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailUsername;
        $mail->Password   = $mailPassword;
        $mail->SMTPSecure = $mailEncryption;
        $mail->Port       = $mailPort;

        $mail->setFrom($mailFrom, $mailFromName);
        $mail->addAddress($to);

        if ($attachmentPath && is_file($attachmentPath)) {
            $mail->addAttachment($attachmentPath, $attachmentName ?? basename($attachmentPath));
        }

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        file_put_contents(
            $logFile,
            date('Y-m-d H:i') . " | PHPMailer ERROR for {$to}: " . $mail->ErrorInfo . "\n",
            FILE_APPEND
        );
        return false;
    }
}

if (!$now->isLastOfMonth()) {
    logMessage($logFile, 'Monthly summary skipped: today is not the last day of the month.');
    return;
}

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$monthKey = $now->format('Y-m');
$stateFile = __DIR__ . "/monthly_summary_{$monthKey}.json";
$state = [
    'month' => $monthKey,
    'sent' => [],
];

if (is_file($stateFile)) {
    $decoded = json_decode((string) file_get_contents($stateFile), true);
    if (is_array($decoded)) {
        $state['sent'] = array_values(array_unique($decoded['sent'] ?? []));
    }
}

$admins = DB::table('users')
    ->select('name', 'email')
    ->where('role', 1)
    ->whereNotNull('email')
    ->where('email', '!=', '')
    ->orderBy('email')
    ->get();

if ($admins->isEmpty()) {
    logMessage($logFile, 'Monthly summary skipped: no admin users with email found.');
    return;
}

$pendingAdmins = $admins->filter(function ($admin) use ($state) {
    return !in_array($admin->email, $state['sent'], true);
})->values();

if ($pendingAdmins->isEmpty()) {
    logMessage($logFile, "Monthly summary skipped: all admins already received report for {$monthKey}.");
    return;
}

$summaryController = app(SummaryController::class);

$baseDate = $now->copy()->startOfMonth();
$period = 'month';
$monthInput = $baseDate->format('Y-m');
$start = $baseDate->copy()->startOfMonth();
$end = $baseDate->copy()->endOfMonth();

$reportTitle = 'Monthly Report - ' .
    $baseDate->format('F Y') .
    ' (' . $start->format('d M') .
    ' - ' . $end->format('d M Y') . ')';

$capacityStats  = $summaryController->getCapacityStats($baseDate, $period);
$devicesByFloor = $summaryController->getDevicesByFloor();
$binAnalytics   = $summaryController->computeBinAnalyticsPerAsset($baseDate, $period);
$assets         = $summaryController->getAssetsPublic();
$cleaningLogs   = $summaryController->getCleaningLogs($baseDate, $period);
$summaryMetrics = $summaryController->computeSummaryMetrics($baseDate, $period);

$labels = $binAnalytics->pluck('asset_name')->values()->all();

$makeChartConfig = function (string $type, string $label, $data, string $border, string $bg) use ($labels): array {
    return [
        'type' => $type,
        'data' => [
            'labels' => $labels,
            'datasets' => [[
                'label' => $label,
                'data' => collect($data)->values()->all(),
                'borderColor' => $border,
                'backgroundColor' => $bg,
                'fill' => true,
                'tension' => 0.3,
            ]],
        ],
    ];
};

$timesFullChartData = buildChartDataUri(
    $makeChartConfig(
        'bar',
        'Times Became Full',
        $binAnalytics->pluck('times_full'),
        '#8e44ad',
        'rgba(142,68,173,0.8)'
    ),
    $logFile
);

$avgFillChartData = buildChartDataUri(
    $makeChartConfig(
        'bar',
        'Average Fill Time (Hours)',
        $binAnalytics->pluck('avg_fill_time'),
        '#2ecc71',
        'rgba(46,204,113,0.8)'
    ),
    $logFile
);

$avgClearChartData = buildChartDataUri(
    $makeChartConfig(
        'bar',
        'Average Clear Time (Hours)',
        $binAnalytics->pluck('avg_clear_time'),
        '#e74c3c',
        'rgba(231,76,60,0.8)'
    ),
    $logFile
);

$pdf = Pdf::loadView('emails.summary_report', [
    'reportTitle'        => $reportTitle,
    'period'             => $period,
    'baseDate'           => $baseDate,
    'capacityStats'      => $capacityStats,
    'devicesByFloor'     => $devicesByFloor,
    'binAnalytics'       => $binAnalytics,
    'assets'             => $assets,
    'cleaningLogs'       => $cleaningLogs,
    'monthInput'         => $monthInput,
    'summaryMetrics'     => $summaryMetrics,
    'timesFullChartData' => $timesFullChartData,
    'avgFillChartData'   => $avgFillChartData,
    'avgClearChartData'  => $avgClearChartData,
])->setPaper('a4', 'portrait');

$pdfDir = storage_path('app/cron-reports');
if (!is_dir($pdfDir)) {
    mkdir($pdfDir, 0777, true);
}

$pdfPath = $pdfDir . DIRECTORY_SEPARATOR . "summary-report-{$monthKey}.pdf";
file_put_contents($pdfPath, $pdf->output());

$subject = 'SmartBin ' . $reportTitle;
$body = "Please find the attached monthly summary report (PDF).\n\nReport: {$reportTitle}";

$successCount = 0;

foreach ($pendingAdmins as $admin) {
    $sent = sendEmailSMTP(
        $admin->email,
        $subject,
        $body,
        $logFile,
        $pdfPath,
        "summary-report-{$monthKey}.pdf"
    );

    if ($sent) {
        $state['sent'][] = $admin->email;
        $state['sent'] = array_values(array_unique($state['sent']));
        file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));
        $successCount++;
        logMessage($logFile, "Monthly summary sent to {$admin->email}");
        continue;
    }

    logMessage($logFile, "Monthly summary failed for {$admin->email}");
}

logMessage(
    $logFile,
    "Monthly summary finished for {$monthKey}: {$successCount}/{$pendingAdmins->count()} admin email(s) sent."
);
