<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\SummaryController;
use Illuminate\Support\Facades\Mail;
use App\Mail\SummaryReportMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SendMonthlySummaryEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:send-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly summary report to all users on the last day of each month at 5:00 PM';

    /**
     * Execute the console command.
     */
    public function handle(SummaryController $summaryController)
    {
        $this->info('Starting monthly summary email job...');

        $baseDate = now()->startOfMonth();
        $period = 'month';
        $monthInput = now()->format('Y-m');

        $start = $baseDate->copy()->startOfMonth();
        $end = $baseDate->copy()->endOfMonth();

        $reportTitle = 'Monthly Report – ' .
            $baseDate->format('F Y') .
            ' (' . $start->format('d M') .
            ' – ' . $end->format('d M Y') . ')';

        // Get all users with email
        $users = User::whereNotNull('email')->get();

        if ($users->count() === 0) {
            $this->warn('No users found. Skipping email sending.');
            return;
        }

        $this->info("Found {$users->count()} active user(s).");

        foreach ($users as $user) {
            try {
                $this->info("Sending report to {$user->email}...");

                $capacityStats = $summaryController->getCapacityStats($baseDate, $period);
                $devicesByFloor = $summaryController->getDevicesByFloor();
                $binAnalytics = $summaryController->computeBinAnalyticsPerAsset($baseDate, $period);
                $assets = $summaryController->getAssetsPublic();
                $cleaningLogs = $summaryController->getCleaningLogs($baseDate, $period);
                $summaryMetrics = $summaryController->computeSummaryMetrics($baseDate, $period);

                // Helper to generate QuickChart URL
                $labels = $binAnalytics->pluck('asset_name')->values();

                $generateChartUrl = function ($type, $label, $data, $border, $bg) use ($labels) {
                    return "https://quickchart.io/chart?c=" . urlencode(json_encode([
                        'type' => $type,
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
                };

                $timesFullChartData = 'data:image/png;base64,' . base64_encode(
                    file_get_contents(
                        $generateChartUrl(
                            'bar',
                            'Times Became Full',
                            $binAnalytics->pluck('times_full')->values(),
                            '#8e44ad',
                            'rgba(142,68,173,0.8)'
                        )
                    )
                );

                $avgFillChartData = 'data:image/png;base64,' . base64_encode(
                    file_get_contents(
                        $generateChartUrl(
                            'bar',
                            'Average Fill Time (Hours)',
                            $binAnalytics->pluck('avg_fill_time')->values(),
                            '#2ecc71',
                            'rgba(46,204,113,0.8)'
                        )
                    )
                );

                $avgClearChartData = 'data:image/png;base64,' . base64_encode(
                    file_get_contents(
                        $generateChartUrl(
                            'bar',
                            'Average Clear Time (Hours)',
                            $binAnalytics->pluck('avg_clear_time')->values(),
                            '#e74c3c',
                            'rgba(231,76,60,0.8)'
                        )
                    )
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

                Mail::to($user->email)->send(
                    new SummaryReportMail([
                        'reportTitle' => $reportTitle
                    ], $pdf->output())
                );

                $this->info("✓ Successfully sent to {$user->email}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to send to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info('Monthly summary email job completed.');
    }
}
