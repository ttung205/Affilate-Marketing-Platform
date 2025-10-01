<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawalOTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Mã OTP rút tiền - ' . config('app.name'))
                    ->view('emails.withdrawal-otp')
                    ->with([
                        'user' => $this->user,
                        'otp' => $this->otp,
                    ]);
    }
}
