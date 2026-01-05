<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SummaryReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $month, $capacityStats, $devicesByFloor, $fullTrend, $fullCounts, $assets;

    public function __construct($month, $capacityStats, $devicesByFloor, $fullTrend, $fullCounts, $assets)
    {
        $this->month = $month;
        $this->capacityStats = $capacityStats;
        $this->devicesByFloor = $devicesByFloor;
        $this->fullTrend = $fullTrend;
        $this->fullCounts = $fullCounts;
        $this->assets = $assets;
    }

    public function build()
    {
        return $this->subject("Summary Report for {$this->month}")
                    ->view('emails.summary_report')
                    ->with([
                        'month' => $this->month,
                        'capacityStats' => $this->capacityStats,
                        'devicesByFloor' => $this->devicesByFloor,
                        'fullTrend' => $this->fullTrend,
                        'fullCounts' => $this->fullCounts,
                        'assets' => $this->assets,
                    ]);
    }
}