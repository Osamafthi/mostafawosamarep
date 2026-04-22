@php /** @var \App\Models\Order $order */ @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Order {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222;">
    <h2>Thanks for your order, {{ $order->customer_name }}!</h2>
    <p>Your order <strong>{{ $order->order_number }}</strong> has been received and is being processed.</p>

    <h3>Summary</h3>
    <table cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse; width:100%; max-width:560px;">
        <thead>
            <tr>
                <th align="left">Product</th>
                <th align="right">Qty</th>
                <th align="right">Unit</th>
                <th align="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td align="right">{{ $item->quantity }}</td>
                    <td align="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td align="right">{{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" align="right"><strong>Total</strong></td>
                <td align="right"><strong>{{ number_format((float) $order->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <p>Shipping to: {{ $order->shipping_address }}</p>
    <p>We'll email you again when your order status changes.</p>
</body>
</html>
