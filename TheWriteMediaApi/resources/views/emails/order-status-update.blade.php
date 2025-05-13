<!DOCTYPE html>
<html>
<head>
    <title>Order Status Update</title>
</head>
<body>
    <h1>Your Order Status Has Been Updated</h1>
    <p>Order #{{ $order->id }} is now {{ $order->status }}.</p>
    
    @if($order->status === 'PROCESSING')
    <p>Shipping fee: ${{ number_format($order->shipping_fee, 2) }}</p>
    <p>New total: ${{ number_format($order->total, 2) }}</p>
    @endif
    
    <p>Thank you for shopping with us!</p>
</body>
</html>