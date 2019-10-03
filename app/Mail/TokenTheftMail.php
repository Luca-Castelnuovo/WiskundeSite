<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Mail\Mailable;

class TokenTheftMail extends Mailable
{
    private $sessionUUID;

    /**
     * Create a new message instance.
     *
     * @param $session_uuid
     *
     * @return void
     */
    public function __construct($session_uuid)
    {
        $this->sessionUUID = $session_uuid;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'mail.TokenTheft',
            [
                'time' => Carbon::now(),
                'session_uuid' => $this->sessionUUID
            ]
        );
    }
}
