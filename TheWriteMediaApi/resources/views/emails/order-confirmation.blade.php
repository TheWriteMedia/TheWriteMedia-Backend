<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
</head>
<body>
    <h1>Thank you for your order!</h1>
    <p>Your order #{{ $order->id }} has been received.</p>
    
    <h2>Order Summary</h2>
    <ul>
        @foreach ($order->items as $item)
            <li>{{ $item['title'] }} - ${{ number_format($item['price'], 2) }} × {{ $item['quantity'] }}</li>
        @endforeach
    </ul>
    
    <p><strong>Total: ${{ number_format($order->total, 2) }}</strong></p>
    
    <h2>Shipping Information</h2>
    <p>
        {{ $order->address_line_one }}<br>
        @if ($order->address_line_two)
            {{ $order->address_line_two }}<br>
        @endif
        {{ $order->city }}, {{ $order->province }}<br>
        {{ $order->country }} {{ $order->postal_code }}
    </p>
    
    <p>If you have any questions about your order, please contact us.</p>
</body>
</html>