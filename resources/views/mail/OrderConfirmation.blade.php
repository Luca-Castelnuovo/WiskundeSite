@component('mail::message')

# Order Confirmation

Hello {{ $user->name }},

This is your order confirmation for order <a href={{ $orderInfoURL }}>{{ $order->id }}</a>. 

@foreach ($products as $product)
    <p>
        Name: {{ $product->name }},
        Subject: {{ $product->subject }},
        Class: {{ $product->class }},
        Method: {{ $product->method }},
        View: <a href="'https://example.com/products/'.{{ $product->id }}">View</a>
    </p>
@endforeach

Thanks,<br>
{{ config('app.name') }}<br>
{{ config('app.domain') }}

@endcomponent
