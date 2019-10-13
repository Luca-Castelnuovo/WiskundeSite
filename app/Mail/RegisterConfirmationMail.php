<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;

class RegisterConfirmationMail extends Mailable
{
    public $user;
    public $verify_mail_token;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $verify_mail_token
     *
     * @return void
     */
    public function __construct($user, $verify_mail_token)
    {
        $this->user = $user;
        $this->verify_mail_token = $verify_mail_token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'mail.RegisterConfirmation',
            [
                'verifyEmailUrl' => config('app.domain') . '/auth/verify/' . $this->verify_mail_token,
            ]
        );
    }
}
