<!DOCTYPE html>
<html>
<head>
    <title>Shipping Fee Update</title>
</head>
<body>
    <h1>Shipping Fee Updated for Your Order</h1>
    
    <p>Your order #{{ $order->id }} has been updated with a shipping fee.</p>
    
    <h2>Order Summary</h2>
    <ul>
        @foreach ($order->items as $item)
            <li>{{ $item['title'] }} - ${{ number_format($item['price'], 2) }} × {{ $item['quantity'] }}</li>
        @endforeach
    </ul>
    
    <div style="margin-top: 20px;">
        <p><strong>Previous Total:</strong> ${{ number_format($oldTotal, 2) }}</p>
        <p><strong>Shipping Fee:</strong> ${{ number_format($order->shipping_fee, 2) }}</p>
        <p><strong>New Total:</strong> ${{ number_format($newTotal, 2) }}</p>
    </div>
    
    <p>Thank you for shopping with us!</p>
</body>
</html>