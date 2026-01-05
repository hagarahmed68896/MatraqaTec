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

        <div class="total">
            الإجمالي: {{ $invoice->amount }} ر.س
        </div>

        <div class="footer">
            <p>شكراً لتعاملكم مع شركة مطرقة تك</p>
        </div>
    </div>
</body>
</html>
