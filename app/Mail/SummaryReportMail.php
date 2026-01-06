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
        return $this->subject('Monthly SmartBin Summary')
            // You can include a simple plain text or html view if you want
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
