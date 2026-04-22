<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emp;
    public $payroll;
    public $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emp, $payroll, $pdf)
    {
        $this->emp = $emp;
        $this->payroll = $payroll;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Salary Slip - ' . $this->payroll->payroll_name)
                    ->view('emails.payslip')
                    ->attachData($this->pdf->output(), 'payslip.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
