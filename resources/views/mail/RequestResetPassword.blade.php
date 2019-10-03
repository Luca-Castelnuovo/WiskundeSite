@component('mail::message')

# Reset Password

Hello {{ $user->name }},

To reset your password, click following link and type in your new password:

@component('mail::button', ['url' => $resetPasswordUrl])
Reset password
@endcomponent

Thanks,<br>
{{ config('app.name') }}<br>
{{ config('app.domain') }}

@endcomponent
