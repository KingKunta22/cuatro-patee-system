<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order</title>
</head>
<body>
    <p>Dear {{ $order->supplier->supplierName }},</p>

    <p>Please find attached the purchase order <strong>{{ $order->orderNumber }}</strong>.</p>

    <p>Delivery Date: {{ $order->deliveryDate }}</p>
    <p>Total Amount: ₱{{ number_format($order->totalAmount, 2) }}</p>

    <p>Thank you,<br>Cuatro Patee Pet Shop</p>
</body>
</html>
