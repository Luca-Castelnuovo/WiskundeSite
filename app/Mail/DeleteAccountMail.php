<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;

class DeleteAccountMail extends Mailable
{
    public $user;

    /**
     * Create a new message instance.
     *
     * @param User $user
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
            'mail.DeleteAccount',
            [
                'contactURL' => config('app.domain').'/contact',
            ]
    );
    }
}
