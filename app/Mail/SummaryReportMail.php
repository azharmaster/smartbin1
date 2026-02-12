<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SummaryReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data; // This will hold all the variables for the view
    public $pdfOutput; // This will hold the generated PDF bytes

    /**
     * Create a new message instance.
     */
    public function __construct(array $data, string $pdfOutput)
    {
        $this->data = $data;
        $this->pdfOutput = $pdfOutput;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $title = $this->data['reportTitle'] ?? 'SmartBin Summary Report';

        return $this->subject('SmartBin ' . $title)
            ->view('emails.blank')
            ->with($this->data)
            ->attachData(
                $this->pdfOutput,
                'summary-report.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }
}
