<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CollectionTripSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $reportTitle,
        public string $pdfOutput,
        public string $filename = 'collection-trip-summary.pdf'
    ) {
    }

    public function build()
    {
        return $this->subject('SmartBin ' . $this->reportTitle)
            ->view('emails.blank')
            ->with([
                'emailMessage' => 'Please find the attached collection trip summary report (PDF).',
                'reportTitle' => $this->reportTitle,
            ])
            ->attachData($this->pdfOutput, $this->filename, [
                'mime' => 'application/pdf',
            ]);
    }
}
