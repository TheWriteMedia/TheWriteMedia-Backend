<!-- resources/views/emails/withdrawal_status.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Withdrawal Request Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
        .status {
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Withdrawal Request Update</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->user_name }},</p>
        
        <p>Your withdrawal request of ${{ number_format($withdrawal->withdraw_value, 2) }} 
        has been marked as <span class="status">{{ strtoupper($status) }}</span>.</p>
        
        @if($status === 'processing')
            <p>We are currently processing your withdrawal request. You'll receive another 
            notification once it's been mailed.</p>
        @elseif($status === 'mailed')
            <p>Your payment has been mailed to your registered address:</p>
            <p>{{ $withdrawal->mailing_address }}</p>
        @elseif($status === 'cancelled')
            <p>The withdrawal request has been cancelled and the amount has been 
            returned to your account balance.</p>
        @elseif($status === 'completed')
            <p>Your withdrawal request is now complete. The payment was received on 
            {{ $withdrawal->date_received->format('F j, Y') }}.</p>
        @endif
    </div>
    
    <div class="footer">
        <p>Thanks,<br>
        {{ config('app.name') }}</p>
    </div>
</body>
</html>