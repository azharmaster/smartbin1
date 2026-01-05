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
        return $this->subject('SmartBin Monthly Summary Report')
                    ->attachData($this->pdf->output(), 'SummaryReport.pdf', [
                        'mime' => 'application/pdf',
                    ])
                    ->view('emails.blank'); // We can use a minimal empty view
    }
}
