@component('mail::message')

    # Token Theft (URGENT)

    Hello,

    Recently a refresh_token theft was detected.
    Time: `{{ $time }}`
    Session UUID: `{{ $session_uuid }}`

    Thanks,<br>
    {{ config('app.name') }}<br>
    {{ config('app.domain') }}

@endcomponent
