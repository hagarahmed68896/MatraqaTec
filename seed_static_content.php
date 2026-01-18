
// Create FAQs
$faqs = [
    [
        'question_ar' => 'كيف يمكنني حجز خدمة صيانة؟',
        'question_en' => 'How can I book a maintenance service?',
        'answer_ar' => 'يمكنك حجز خدمة الصيانة من خلال التطبيق عبر اختيار نوع الخدمة، تحديد الموعد المناسب، ثم تأكيد الحجز.',
        'answer_en' => 'You can book a maintenance service through the app by selecting the service type, choosing a suitable time, then confirming the booking.',
        'target_group' => 'clients',
        'status' => 'active'
    ],
    [
        'question_ar' => 'ما هي طرق الدفع المتاحة؟',
        'question_en' => 'What payment methods are available?',
        'answer_ar' => 'نوفر عدة طرق للدفع: المحفظة الإلكترونية، بطاقات الائتمان، Apple Pay، والدفع النقدي عند الاستلام.',
        'answer_en' => 'We offer several payment methods: E-wallet, credit cards, Apple Pay, and cash on delivery.',
        'target_group' => 'clients',
        'status' => 'active'
    ],
    [
        'question_ar' => 'كيف يمكنني إلغاء الطلب؟',
        'question_en' => 'How can I cancel an order?',
        'answer_ar' => 'يمكنك إلغاء الطلب من خلال صفحة "طلباتي" قبل بدء الفني بالعمل. سيتم استرداد المبلغ إلى محفظتك الإلكترونية.',
        'answer_en' => 'You can cancel the order through the "My Orders" page before the technician starts work. The amount will be refunded to your e-wallet.',
        'target_group' => 'clients',
        'status' => 'active'
    ],
    [
        'question_ar' => 'كم يستغرق وصول الفني؟',
        'question_en' => 'How long does it take for the technician to arrive?',
        'answer_ar' => 'يعتمد ذلك على الموعد المحدد والموقع. عادة يصل الفني في الموعد المحدد أو قبله بقليل.',
        'answer_en' => 'It depends on the scheduled time and location. Usually, the technician arrives at or slightly before the scheduled time.',
        'target_group' => 'clients',
        'status' => 'active'
    ],
    [
        'question_ar' => 'هل يمكنني تتبع موقع الفني؟',
        'question_en' => 'Can I track the technician\'s location?',
        'answer_ar' => 'نعم، يمكنك تتبع موقع الفني في الوقت الفعلي من خلال صفحة تفاصيل الطلب.',
        'answer_en' => 'Yes, you can track the technician\'s location in real-time through the order details page.',
        'target_group' => 'clients',
        'status' => 'active'
    ]
];

foreach ($faqs as $faq) {
    \App\Models\Faq::create($faq);
}

echo "✅ Created " . count($faqs) . " FAQs\n";

// Create Terms & Conditions
$terms = [
    'title_ar' => 'الشروط والأحكام',
    'title_en' => 'Terms & Conditions',
    'content_ar' => "# الشروط والأحكام

## 1. القبول
باستخدامك لهذا التطبيق، فإنك توافق على الالتزام بهذه الشروط والأحكام.

## 2. الخدمات
نحن نوفر منصة لربطك بمقدمي خدمات الصيانة المعتمدين.

## 3. الحجز والإلغاء
- يجب تأكيد الحجز قبل 24 ساعة على الأقل
- يمكن إلغاء الحجز مع استرداد كامل المبلغ قبل بدء العمل
- لا يمكن استرداد المبلغ بعد بدء الفني بالعمل

## 4. المسؤولية
- نحن غير مسؤولين عن أي أضرار ناتجة عن سوء استخدام الخدمة
- جودة العمل مضمونة من قبل مقدم الخدمة

## 5. الدفع
- جميع الأسعار شاملة ضريبة القيمة المضافة
- الدفع مطلوب قبل أو بعد إتمام الخدمة حسب الطريقة المختارة",
    'content_en' => "# Terms & Conditions

## 1. Acceptance
By using this application, you agree to comply with these terms and conditions.

## 2. Services
We provide a platform to connect you with certified maintenance service providers.

## 3. Booking and Cancellation
- Booking must be confirmed at least 24 hours in advance
- Full refund available upon cancellation before work begins
- No refund after technician starts work

## 4. Liability
- We are not responsible for any damages resulting from misuse of the service
- Work quality is guaranteed by the service provider

## 5. Payment
- All prices include VAT
- Payment required before or after service completion depending on chosen method",
    'target_group' => 'clients',
    'status' => 'active'
];

\App\Models\Term::create($terms);
echo "✅ Created Terms & Conditions\n";

// Create Privacy Policy
$privacy = [
    'title_ar' => 'سياسة الخصوصية',
    'title_en' => 'Privacy Policy',
    'content_ar' => "# سياسة الخصوصية

## المعلومات التي نجمعها
نحن نجمع المعلومات التالية:
- الاسم ومعلومات الاتصال
- عنوان الموقع لتقديم الخدمة
- معلومات الدفع (مشفرة)
- سجل الطلبات والمعاملات

## كيف نستخدم المعلومات
- لتقديم وتحسين خدماتنا
- للتواصل معك بخصوص طلباتك
- لمعالجة المدفوعات
- لإرسال إشعارات مهمة

## حماية المعلومات
نحن نستخدم بروتوكولات أمان متقدمة لحماية بياناتك:
- تشفير SSL/TLS
- خوادم آمنة
- الوصول المحدود للبيانات

## مشاركة المعلومات
لن نشارك معلوماتك الشخصية مع أطراف ثالثة إلا:
- عند الضرورة لتقديم الخدمة
- بموافقتك الصريحة
- عند طلب السلطات القانونية

## حقوقك
يحق لك:
- الوصول إلى بياناتك الشخصية
- طلب تصحيح أو حذف بياناتك
- الاعتراض على معالجة بياناتك

للاستفسارات: support@matraqatec.com",
    'content_en' => "# Privacy Policy

## Information We Collect
We collect the following information:
- Name and contact information
- Location address for service delivery
- Payment information (encrypted)
- Order and transaction history

## How We Use Information
- To provide and improve our services
- To communicate with you about your orders
- To process payments
- To send important notifications

## Information Protection
We use advanced security protocols to protect your data:
- SSL/TLS encryption
- Secure servers
- Limited data access

## Information Sharing
We will not share your personal information with third parties except:
- When necessary to provide the service
- With your explicit consent
- Upon legal authority request

## Your Rights
You have the right to:
- Access your personal data
- Request correction or deletion of your data
- Object to data processing

For inquiries: support@matraqatec.com",
    'target_group' => 'all',
    'status' => 'active'
];

\App\Models\Term::create($privacy);
echo "✅ Created Privacy Policy\n";

echo "\n🎉 All dummy data created successfully!\n";
