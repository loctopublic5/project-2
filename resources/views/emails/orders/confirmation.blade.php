<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác nhận đơn hàng</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">

<div style="width: 100%; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 5px; overflow: hidden;">
    <div style="background-color: #4F46E5; color: #ffffff; padding: 20px; text-align: center;">
        <h1 style="margin: 0;">Cảm ơn bạn đã đặt hàng!</h1>
    </div>

    <div style="padding: 20px; background-color: #ffffff;">
        <p>Xin chào <strong>{{ $notifiable->full_name ?? 'Quý khách' }}</strong>,</p>
        <p>Đơn hàng <strong>#{{ $order->id }}</strong> của bạn đã được ghi nhận. Chúng tôi sẽ sớm giao hàng cho bạn.</p>

        <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
            <strong>Thông tin nhận hàng:</strong><br>
            Người nhận: {{ $order->shipping_name }}<br>
            SĐT: {{ $order->shipping_phone }}<br>
            Địa chỉ: {{ $order->shipping_address }}
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #eee;">
                    <th style="text-align: left; padding: 10px; border-bottom: 2px solid #ddd;">Sản phẩm</th>
                    <th style="text-align: center; padding: 10px; border-bottom: 2px solid #ddd;">SL</th>
                    <th style="text-align: right; padding: 10px; border-bottom: 2px solid #ddd;">Giá</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">
                        {{ $item->product->name ?? 'Sản phẩm' }}
                        @if($item->variant) <br><small>({{ $item->variant }})</small> @endif
                    </td>
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #eee;">{{ $item->quantity }}</td>
                    <td style="text-align: right; padding: 10px; border-bottom: 1px solid #eee;">
                        {{ number_format($item->price, 0, ',', '.') }} đ
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: right;">
            <p>Phí vận chuyển: {{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }} đ</p>
            <h3 style="color: #4F46E5;">Tổng thanh toán: {{ number_format($order->total_amount, 0, ',', '.') }} đ</h3>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Xem chi tiết đơn hàng</a>
        </div>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #888;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}.</p>
    </div>
</div>

</body>
</html>