<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class WithdrawalStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $withdrawal;
    public $status;
    public $user;

    public function __construct($withdrawal, $status, $user)
    {
        $this->withdrawal = $withdrawal;
        $this->status = $status;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject("Withdrawal Request {$this->status}")
            ->view('emails.withdrawal_status');
    }
}