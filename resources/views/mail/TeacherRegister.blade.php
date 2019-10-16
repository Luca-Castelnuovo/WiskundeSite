@component('mail::message')

# Register Confirmation

Hello Administrator,

A teacher account was created successfully.

Name: {{ $user->name }}
Email: {{ $user->email }}

Please click following link to verify the account:

@component('mail::button', ['url' => $verifyAccountURL])
Verify teacher
@endcomponent

Thanks,<br>
{{ config('app.name') }}<br>
{{ config('app.domain') }}

@endcomponent
