@component('mail::message')

# Product update

Hello {{ $user->name }},

We have reviewed your product {{ $product->name }}({{ $product->id }}), 

The new state is now: {{ $state }}
The reason for this state is: {{ $reason }}

@component('mail::button', ['url' => $viewProductURL])
View Product
@endcomponent

Thanks,<br>
{{ config('app.name') }}<br>
{{ config('app.domain') }}

@endcomponent
