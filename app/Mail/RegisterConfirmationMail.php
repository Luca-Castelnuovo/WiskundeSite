<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;


class RegisterConfirmationMail extends Mailable
{
	public $user;

    /**
     * Create a new message instance.
     *
     * @param User
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
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
                'verifyEmailUrl' => config('app.domain') . '/auth/verify/' . $this->user->verify_email_token
            ]
        );
    }
}