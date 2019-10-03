@component('mail::message')

# Register Confirmation

Hello {{ $user->name }},

Your account has been created successfully.

Please click following link to verify your email address:

@component('mail::button', ['url' => $verifyEmailUrl])
Verify your email address
@endcomponent

Thanks,<br>
{{ config('app.name') }}<br>
{{ config('app.domain') }}

@endcomponent
