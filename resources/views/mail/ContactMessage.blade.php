@component('mail::message')

# Contact Message

A contact message has been sent:

<strong>Name</strong> <br>
{{ $data['name'] }}

<strong>Email</strong> <br>
{{ $data['email'] }}

<strong>Message</strong>
@component('mail::panel')
  {{ $data['message'] }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}<br>
{{ config('app.domain') }}

@endcomponent
