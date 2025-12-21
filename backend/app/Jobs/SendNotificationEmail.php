<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public $email, public $message) {}

    public function handle()
    {
        Mail::raw($this->message, function ($mail) {
            $mail->to($this->email)
                 ->subject('Notification');
        });
    }
}
