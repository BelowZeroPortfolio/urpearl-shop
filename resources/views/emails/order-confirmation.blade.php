<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #ec4899, #f472b6);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .order-info {
            background-color: #fef7f0;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .order-info h2 {
            color: #ea580c;
            margin: 0 0 15px 0;
            font-size: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #7c2d12;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #475569;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .items-table tr:hover {
            background-color: #f8fafc;
        }
        .total-row {
            background-color: #fef2f2;
            font-weight: 600;
            color: #dc2626;
        }
        .shipping-info {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .shipping-info h3 {
            color: #0369a1;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-shipped {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmation</h1>
            <p>Thank you for your purchase, {{ $user->name }}!</p>
        </div>

        <div class="content">
            <div class="order-info">
                <h2>Order Details</h2>
                <div class="info-row">
                    <span class="info-label">Order Number:</span>
                    <span>#{{ $order->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order Date:</span>
                    <span>{{ $order->created_at->format('F j, Y \a\t g:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-{{ $order->status->value }}">
                        {{ ucfirst($order->status->value) }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Amount:</span>
                    <span style="font-size: 18px; font-weight: 600; color: #dc2626;">
                        ₱{{ number_format($order->total_amount, 2) }}
                    </span>
                </div>
            </div>

            <h3 style="color: #374151; margin-bottom: 15px;">Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderItems as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            <br>
                            <small style="color: #6b7280;">SKU: {{ $item->product->sku }}</small>
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>₱{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>₱{{ number_format($order->total_amount, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            @if($order->shipping_address)
            <div class="shipping-info">
                <h3>Shipping Address</h3>
                @if(is_array($order->shipping_address))
                    <p>
                        @if(isset($order->shipping_address['name']))
                            <strong>{{ $order->shipping_address['name'] }}</strong><br>
                        @endif
                        @if(isset($order->shipping_address['address_line_1']))
                            {{ $order->shipping_address['address_line_1'] }}<br>
                        @endif
                        @if(isset($order->shipping_address['address_line_2']) && $order->shipping_address['address_line_2'])
                            {{ $order->shipping_address['address_line_2'] }}<br>
                        @endif
                        @if(isset($order->shipping_address['city']))
                            {{ $order->shipping_address['city'] }}
                        @endif
                        @if(isset($order->shipping_address['state']))
                            , {{ $order->shipping_address['state'] }}
                        @endif
                        @if(isset($order->shipping_address['postal_code']))
                            {{ $order->shipping_address['postal_code'] }}
                        @endif
                        @if(isset($order->shipping_address['country']))
                            <br>{{ $order->shipping_address['country'] }}
                        @endif
                    </p>
                @else
                    <p>{{ $order->shipping_address }}</p>
                @endif
            </div>
            @endif

            <div style="margin-top: 30px; padding: 20px; background-color: #f9fafb; border-radius: 8px;">
                <h3 style="color: #374151; margin-top: 0;">What's Next?</h3>
                <p style="margin-bottom: 10px;">
                    @if($order->status->value === 'pending')
                        We're processing your order and will send you an update once it's confirmed.
                    @elseif($order->status->value === 'paid')
                        Your payment has been confirmed! We're preparing your order for shipment.
                    @elseif($order->status->value === 'shipped')
                        Your order has been shipped! You should receive it soon.
                    @endif
                </p>
                <p style="color: #6b7280; font-size: 14px;">
                    You can track your order status by logging into your account on our website.
                </p>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for shopping with UrPearl SHOP!</p>
            <p>If you have any questions about your order, please contact our customer support.</p>
            <p style="margin-top: 15px; font-size: 12px;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>