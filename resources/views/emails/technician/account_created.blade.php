<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .header { background: #1A1A31; padding: 40px; text-align: center; color: white; }
        .content { padding: 40px; line-height: 1.6; color: #334155; text-align: right; }
        .credentials { background: #f1f5f9; padding: 25px; border-radius: 12px; margin: 30px 0; }
        .footer { padding: 30px; text-align: center; font-size: 12px; color: #64748b; background: #f8fafc; }
        .button { display: inline-block; padding: 15px 30px; background: #1A1A31; color: white; text-decoration: none; border-radius: 10px; font-weight: bold; margin-top: 20px; }
        .label { font-weight: bold; color: #1A1A31; }
        .value { color: #64748b; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 24px;">مرحباً بك في أسرة مطرقة تك</h1>
        </div>
        <div class="content">
            <h2 style="color: #1A1A31;">تم قبول طلب انضمامك بنجاح!</h2>
            <p>يسعدنا إبلاغك بأنه قد تم تفعيل حسابك كفني في منصة مطرقة تك. يمكنك الآن البدء باستقبال الطلبات وتقديم خدماتك.</p>
            
            <p>فيما يلي بيانات الدخول الخاصة بحسابك:</p>
            
            <div class="credentials">
                <div style="margin-bottom: 10px;">
                    <span class="label">البريد الإلكتروني:</span>
                    <span class="value">{{ $email }}</span>
                </div>
                <div>
                    <span class="label">كلمة المرور:</span>
                    <span class="value">{{ $password }}</span>
                </div>
            </div>

            <p>يرجى تغيير كلمة المرور الخاصة بك بعد تسجيل الدخول لأول مرة لضمان أمان حسابك.</p>
            
            <a href="#" class="button">تسجيل الدخول للتطبيق</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} مطرقة تك. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
