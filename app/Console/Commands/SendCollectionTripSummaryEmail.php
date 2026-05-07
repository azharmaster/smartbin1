<?php

namespace App\Console\Commands;

use App\Http\Controllers\CollectionTripController;
use App\Mail\CollectionTripSummaryMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendCollectionTripSummaryEmail extends Command
{
    protected $signature = 'collection-trips:send-summary-email
                            {--period=monthly : daily, weekly, or monthly}
                            {--date= : Date for daily period (Y-m-d)}
                            {--week= : ISO week for weekly period (Y-\WW)}
                            {--month= : Month for monthly period (Y-m)}
                            {--asset_id= : Optional asset id filter}
                            {--capacity_filter=empty : empty, half, or full}
                            {--to=* : Override recipients for testing}';

    protected $description = 'Send the collection trip summary PDF report to all admin email addresses.';

    public function handle(CollectionTripController $collectionTripController): int
    {
        $filters = $this->buildFilters();
        $recipients = $this->resolveRecipients();

        if ($recipients->isEmpty()) {
            $this->warn('No admin recipients with email addresses found. Skipping email sending.');

            return self::SUCCESS;
        }

        $this->info('Generating collection trip summary PDF...');

        try {
            $summaryData = $collectionTripController->getSummaryViewData($filters);
            $pdfOutput = $collectionTripController->generateSummaryPdf($filters);
        } catch (\Throwable $e) {
            $this->error('Failed to generate collection trip summary PDF: ' . $e->getMessage());

            return self::FAILURE;
        }

        $reportTitle = 'Collection Trip Summary - ' . $summaryData['rangeLabel'];
        $filename = 'collection-trip-summary-' . str($summaryData['rangeLabel'])->slug() . '.pdf';

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new CollectionTripSummaryMail($reportTitle, $pdfOutput, $filename));
                $this->info("Sent summary report to {$email}");
            } catch (\Throwable $e) {
                $this->error("Failed sending summary report to {$email}: " . $e->getMessage());
            }
        }

        $this->info('Collection trip summary email job completed.');

        return self::SUCCESS;
    }

    private function buildFilters(): array
    {
        $period = (string) $this->option('period');
        $now = Carbon::now();

        $filters = [
            'period' => in_array($period, ['daily', 'weekly', 'monthly'], true) ? $period : 'monthly',
            'capacity_filter' => (string) $this->option('capacity_filter') ?: 'empty',
        ];

        if ($assetId = $this->option('asset_id')) {
            $filters['asset_id'] = (int) $assetId;
        }

        if ($filters['period'] === 'daily') {
            $filters['date'] = (string) ($this->option('date') ?: $now->toDateString());
        } elseif ($filters['period'] === 'weekly') {
            $filters['week'] = (string) ($this->option('week') ?: $now->format('Y-\WW'));
        } else {
            $filters['month'] = (string) ($this->option('month') ?: $now->format('Y-m'));
        }

        return $filters;
    }

    private function resolveRecipients()
    {
        $overrideRecipients = collect((array) $this->option('to'))
            ->filter()
            ->values();

        if ($overrideRecipients->isNotEmpty()) {
            $this->info('Using overridden recipients: ' . $overrideRecipients->implode(', '));

            return $overrideRecipients;
        }

        return User::query()
            ->where('role', 1)
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->values();
    }
}
