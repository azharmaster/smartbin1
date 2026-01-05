<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class SummaryReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdf;

    /**
     * Create a new message instance.
     */
    public function __construct($pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Monthly SmartBin Summary')
            ->view('emails.summary_report')
            ->attachData(
                $this->pdf->output(),
                'summary-report.pdf'
            );
    }
}
