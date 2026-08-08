<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة جديدة</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        .table th { background-color: #f8f8f8; }
        .total { text-align: left; font-size: 1.2em; font-weight: bold; }
        .footer { text-align: center; font-size: 0.8em; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>فاتورة جديدة</h1>
            <p>عميلنا العزيز {{ $invoice->order->user->name ?? 'العميل' }}</p>
        </div>
        
        <div class="details">
            <p><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number ?? $invoice->id }}</p>
            <p><strong>تاريخ الفاتورة:</strong> {{ $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : now()->format('Y-m-d') }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>الوصف</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->order && $invoice->order->service_id)
                <tr>
                    <td>{{ $invoice->order->service->name_ar ?? 'خدمة' }}</td>
                    <td>{{ $invoice->amount }} ر.س</td>
                </tr>
                @else
                <tr>
                    <td>قطع غيار / خدمات أخرى</td>
                    <td>{{ $invoice->amount }} ر.س</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 20px; font-size: 0.9em; color: #555; text-align: left; line-height: 1.6;">
            @if($invoice->order && $invoice->order->discount_amount > 0)
                <p><strong>السعر الأصلي:</strong> {{ number_format($invoice->order->original_price_before_tax, 2) }} ر.س</p>
                <p style="color: #e53e3e;"><strong>الخصم:</strong> -{{ number_format($invoice->order->discount_amount, 2) }} ر.س</p>
            @endif
            <p><strong>المبلغ الخاضع للضريبة:</strong> {{ number_format($invoice->amount, 2) }} ر.س</p>
            <p><strong>ضريبة القيمة المضافة (15%):</strong> {{ number_format($invoice->tax, 2) }} ر.س</p>
            <h3 style="font-size: 1.3em; color: #1a1a31; margin-top: 10px;"><strong>الإجمالي شامل الضريبة:</strong> {{ number_format($invoice->total, 2) }} ر.س</h3>
        </div>

        <div style="text-align: center; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            <p style="font-size: 0.8em; color: #888; margin-bottom: 8px;">رمز الفاتورة الإلكترونية (ZATCA QR Code)</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($invoice->zatca_qr_code) }}" alt="QR Code" style="border: 1px solid #eee; padding: 5px; border-radius: 4px;">
        </div>

        <div class="footer">
            <p>شكراً لتعاملكم مع شركة مطرقة تك</p>
        </div>
    </div>
</body>
</html>
