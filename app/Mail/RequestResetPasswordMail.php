<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;

class RequestResetPasswordMail extends Mailable
{
    public $user;
	public $reset_password_token;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $reset_password_token
     *
     * @return void
     */
    public function __construct($user, $reset_password_token)
    {
        $this->user = $user;
        $this->reset_password_token = $reset_password_token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'mail.RequestResetPassword',
            [
                'resetPasswordUrl' => config('app.domain') . '/auth/reset/' . $this->reset_password_token
            ]
        );
    }
}
