@php /** @var \App\Models\Order $order */ @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New order {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222;">
    <h2>New order received: {{ $order->order_number }}</h2>
    <p><strong>Customer:</strong> {{ $order->customer_name }} &lt;{{ $order->customer_email }}&gt;</p>
    @if ($order->customer_phone)
        <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
    @endif
    <p><strong>Ship to:</strong> {{ $order->shipping_address }}</p>

    <h3>Items</h3>
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

    <p>Status: {{ $order->status }} / Payment: {{ $order->payment_status }}</p>
</body>
</html>
