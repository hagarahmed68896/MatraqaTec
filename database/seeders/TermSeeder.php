<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Term;

class TermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing 'all' target group terms to avoid duplicates
        Term::where('target_group', 'all')->delete();

        Term::create([
            'title_ar' => 'شروط الاستخدام والأحكام لتطبيق “مطرقة تيك” (Matraqa Tec)',
            'title_en' => 'Terms of Use and Conditions for “Matraqa Tec” Application',
            'content_ar' => "1. المقدمة والقبول القانوني
مرحبًا بكم في تطبيق “مطرقة تيك \" (Matraqa Tec ) ويشار إليه فيما بعد بـ “التطبيق” أو “المنصة .
إن الوصول إلى التطبيق أو استخدامه أو التسجيل فيه يُعد بمثابة إقرار صريح من قبلك بأنك قد قرأت هذه الشروط والأحكام وفهمتها، وتوافق على الالتزام بها التزامًا قانونيًا كاملاً، بالإضافة إلى سياسة الاستخدام المعتمدة.
وبناءً عليه، يُوصى بمراجعة هذه الشروط بعناية قبل استخدام المنصة، حيث تُشكّل اتفاقية ملزمة قانونًا بينك وبين إدارة التطبيق، وتنظّم حقوق والتزامات كل طرف فيما يتعلق بالوصول إلى خدمات المنصة واستخدامها.
وفي حال عدم موافقتك على أي حكم من الأحكام الواردة في هذه الوثيقة، يجب عليك الامتناع عن استخدام التطبيق والتوقف فورًا عن الوصول إلى خدماته.

2. التعريفات
لأغراض هذه الوثيقة، يكون للمصطلحات التالية المعاني المبينة أدناه، ما لم يقتضِ السياق خلاف ذلك:
2.1 التطبيق / المنصة
يقصد به منصة “مطرقة تيك\"( Matraqa Tec ) الإلكترونية، ويشمل ذلك كافة الأنظمة، والبرمجيات، والخدمات الرقمية المرتبطة بها أو التابعة لها.
2.2 المستخدم (العميل)
يقصد به أي شخص طبيعي أو اعتباري يقوم باستخدام التطبيق أو التسجيل فيه للاستفادة من الخدمات المتاحة من خلاله.
2.3 مقدم الخدمة
يقصد به أي شخص طبيعي (فني أو مهني) أو شخص اعتباري (شركة، مؤسسة، مكتب هندسي) يقدم خدمات عبر المنصة للمستخدمين.
2.4 الخدمة
يقصد بها أي خدمة يتم طلبها أو تقديمها من خلال التطبيق، بما في ذلك – على سبيل المثال لا الحصر – خدمات الصيانة، والتشطيبات، والمقاولات، والبناء، والخدمات اللوجستية، أو الخدمات اليومية.
2.5 التعاملات خارج المنصة
يقصد بها أي تواصل، أو اتفاق، أو ترتيب مالي، أو عملية دفع تتم بين المستخدم ومقدم الخدمة خارج القنوات الرسمية المعتمدة للمنصة.

3. الأهلية وتسجيل الحساب
3.1 السن القانونية
يجب أن يكون المستخدم قد أتم الثامنة عشرة (18) من عمره، وأن يتمتع بالأهلية القانونية الكاملة التي تخوله لإبرام عقود ملزمة قانونًا واستخدام المنصة.
3.2 دقة المعلومات
يلتزم كل من المستخدمين ومقدمي الخدمة بتقديم معلومات صحيحة وكاملة ومحدثة عند التسجيل أو استخدام الخدمات، ويحق للمنصة التحقق من الهوية والمستندات من خلال الوسائل المعتمدة لضمان الامتثال للأنظمة.
3.3 سرية الحساب
يتحمل المستخدم مسؤولية الحفاظ على سرية بيانات الدخول الخاصة به، ويُحظر عليه تمكين أي طرف ثالث من استخدام حسابه، كما يتحمل كامل المسؤولية عن جميع الأنشطة التي تتم من خلال حسابه.

4. سياسة الاستخدام المقبول
يلتزم المستخدمون ومقدمو الخدمة باستخدام التطبيق بما يتفق مع الأنظمة واللوائح السارية، ويشمل ذلك على وجه الخصوص:
- استخدام المنصة بصورة قانونية ومتوافقة مع أنظمة المملكة العربية السعودية
- الامتناع عن إساءة استخدام المنصة، بما في ذلك محاولات الاختراق أو تعطيل الأنظمة أو الإضرار بها
- عدم نشر أو إرسال أي محتوى غير قانوني أو مسيء أو ضار
- عدم استخدام المنصة لأي أغراض احتيالية أو غير مشروعة، بما في ذلك التلاعب بالتقييمات أو آليات النظام

5. الخدمات والتسعير والتشغيل
5.1 تعديل الخدمات والأسعار
تحتفظ المنصة بحقها في تعديل أو تحديث الخدمات أو الأسعار المعروضة في أي وقت، بما يتماشى مع الاحتياجات التشغيلية والأنظمة ذات الصلة.
5.2 النطاق الجغرافي
قد تختلف إمكانية توفر الخدمات حسب الموقع الجغرافي، وذلك وفقًا لتوفر مقدمي الخدمة المؤهلين في المنطقة.
5.3 الاتفاقيات خارج المنصة
لا تتحمل المنصة أي مسؤولية عن أي اتفاقيات أو ترتيبات تتم خارج نطاقها الرسمي.

6. سياسة الدفع والاسترداد والإلغاء
6.1 طرق الدفع
يجب إجراء جميع عمليات الدفع من خلال وسائل الدفع الإلكترونية المعتمدة داخل التطبيق فقط.
6.2 دور المنصة واسترداد المحفظة
تعمل منصة “مطرقة تيك” (Matraqa Tec ) كوسيط تقني لربط المستخدمين بمقدمي الخدمة، وفي الحالات المعتمدة للإلغاء أو النزاعات، يتم إعادة المبالغ إلى المحفظة الإلكترونية الخاصة بالمستخدم داخل التطبيق.
6.3 عدم القابلية للتحويل
لا يجوز سحب المبالغ المستردة نقدًا، أو إرجاعها إلى البطاقات البنكية، أو تحويلها خارج التطبيق، ويقتصر استخدامها على الخدمات المستقبلية داخل المنصة.
6.4 المراجعة المالية
تخضع كافة العمليات والطلبات المالية للمراجعة والموافقة، ويجوز رفضها أو تعديلها في حال الاشتباه بوجود إساءة استخدام أو احتيال.
6.5 الإلغاء بعد تحرك مقدم الخدمة
يجوز للمستخدم إلغاء الطلب دون رسوم قبل شروع مقدم الخدمة في التحرك، أما بعد بدء التحرك، فلا يحق استرداد المبلغ، ويُعتبر المبلغ المدفوع تعويضًا عن الوقت وتكاليف التنقل.

7. التزامات مقدمي الخدمة (تفصيلية)
يلتزم مقدمو الخدمة بالآتي:
- تقديم الخدمات بمستوى مهني عالٍ وفق المعايير المعترف بها
- الامتثال التام لكافة الأنظمة والتعليمات المعمول بها
- تقديم معلومات صحيحة ودقيقة بشأن خدماتهم
- تنفيذ الطلبات المتفق عليها ضمن الإطار الزمني المحدد
- الالتزام بالسلوك المهني والأخلاقي
- ضمان جودة الأعمال ومعالجة الشكاوى
- الالتزام بمتطلبات السلامة والصحة المهنية
- الحفاظ على سرية بيانات المستخدمين
- استخدام المنصة بشكل نظامي وعدم التحايل عليها
- سرعة الاستجابة والتواصل الفعال
- تحمل المسؤولية القانونية الكاملة عن الأعمال، وتعويض المنصة عن أي مطالبات

8. حظر التعامل خارج المنصة
8.1 المخالفات
تشمل المخالفات تبادل أرقام الاتصال أو الاتفاق على الدفع خارج المنصة.
8.2 الإجراءات
يحق للمنصة تعليق الحسابات، أو حظرها، أو حجز المستحقات المالية في حال المخالفة.
8.3 المسؤولية
لا تتحمل المنصة أي مسؤولية عن أي تعامل يتم خارجها.
8.4 الإقرار
يُقر جميع الأطراف بأن المنصة هي القناة الرسمية والوحيدة لإتمام المعاملات.

9. تحديد المسؤولية
9.1 طبيعة المنصة
تعمل المنصة كوسيط تقني فقط، ولا تتحمل مسؤولية تنفيذ الخدمة نفسها.
9.2 حدود المسؤولية
لا تتحمل المنصة أي مسؤولية عن الأضرار المباشرة أو غير المباشرة أو العرضية أو التبعية الناتجة عن استخدام الخدمة أو تعذر استخدامها.

10. الملكية الفكرية
10.1 حقوق المنصة
جميع حقوق الملكية الفكرية، بما في ذلك العلامات التجارية والتصاميم والبرمجيات، مملوكة للمنصة ولا يجوز استخدامها دون إذن مسبق.
10.2 محتوى المستخدم
يمنح المستخدم المنصة ترخيصًا غير حصري لاستخدام المحتوى الذي يقوم برفعه لأغراض تشغيلية وتسويقية.

11. الخصوصية وحماية البيانات
11.1 بيانات الموقع
يوافق المستخدم على جمع واستخدام بيانات الموقع لأغراض تقديم الخدمة.
11.2 السرية
يلتزم مقدمو الخدمة بحماية معلومات المستخدمين وعدم إفشائها.
11.3 حقوق البيانات
يحق للمستخدم الوصول إلى بياناته وتعديلها أو حذفها وفقًا للأنظمة المعمول بها.

12. التعديلات وإيقاف الحساب
12.1 التعديلات
تحتفظ المنصة بحق تعديل الشروط والاحكام في أي وقت.
12.2 الاستمرار في الاستخدام
يُعد استمرار المستخدم في استخدام المنصة موافقة صريحة على التعديلات.
12.3 إنهاء الحساب
يجوز للمنصة تعليق أو إنهاء الحساب في حال مخالفة الشروط.

13. القانون والاختصاص القضائي
تخضع هذه الشروط لأنظمة وقوانين المملكة العربية السعودية، وتختص المحاكم السعودية بالنظر في أي نزاع ينشأ عنها.

14. معلومات التواصل
يجب على المستخدمين التواصل مع المنصة من خلال القنوات الرسمية المعتمدة للاستفسارات أو الشكاوى.",
            'content_en' => "1. Introduction and Legal Acceptance
Welcome to the “Matraqa Tec” application (hereinafter referred to as the “Application” or the “Platform”).
Your access to, use of, or registration for the Application constitutes your explicit acknowledgment that you have read, understood, and agreed to be legally bound by these Terms and Conditions and the Usage Policy in full.
Accordingly, you are advised to carefully review these terms prior to using the Platform, as they constitute a legally binding agreement between you and the Application’s management, governing the rights and obligations of each party with respect to access to and use of the Platform’s services.
If you do not agree to any provision contained herein, you must refrain from using the Application and discontinue access to its services immediately.

2. Definitions
For the purposes of this document, the following terms shall have the meanings assigned to them, unless the context requires otherwise:
2.1 Application / Platform
Refers to the “Matraqa Tec” electronic platform, including all related systems, software, and associated digital services.
2.2 User (Client)
Any natural or legal person who uses or registers in the Application for the purpose of benefiting from the services made available through it.
2.3 Service Provider
Any natural person (technician or professional) or legal entity (such as a company or engineering office) that provides services to users through the Application.
2.4 Service
Any service, including but not limited to maintenance, finishing, contracting, construction, logistics, or daily services, requested or provided through the Application.
2.5 Off-Platform Transactions
Any communication, agreement, financial arrangement, or payment carried out between the user and the service provider outside the official channels of the Platform.

3. Eligibility and Account Registration
3.1 Legal Age
Users must be at least 18 years old and possess full legal capacity to enter into binding agreements and use the Platform.
3.2 Accuracy of Information
Users and service providers must provide accurate, complete, and up-to-date information during registration and use of the services. The Platform reserves the right to verify identity and documentation through approved channels to ensure regulatory compliance.
3.3 Account Confidentiality
Users are responsible for maintaining the confidentiality of their account credentials and must not allow any third party to access or use their account. Users bear full responsibility for all activities conducted under their account.

4. Acceptable Use Policy
Users and service providers agree to use the Application in accordance with applicable laws and regulations, including:
4.1 Using the Platform legally and in compliance with the laws and regulations of the Kingdom of Saudi Arabia.
4.2 Refraining from any misuse, including attempts to hack, disrupt, or compromise the Platform’s systems.
4.3 Not publishing or transmitting unlawful, offensive, or harmful content.
4.4 Not using the Platform for fraudulent or illegal purposes, including manipulation of ratings or internal mechanisms.

5. Services, Pricing, and Operations
5.1 Service and Price Modifications
Services and prices displayed on the Platform may be updated or modified at any time in accordance with operational requirements and applicable regulations.
5.2 Geographic Scope
Availability of services may vary depending on geographic location and the availability of qualified service providers.
5.3 Off-Platform Agreements
The Platform bears no responsibility for any agreements or arrangements made outside the Platform.

6. Payment, Refund, and Cancellation Policy
6.1 Payment Methods
Payments must be made exclusively through the approved electronic payment methods available within the Application.
6.2 Platform Role and Wallet Refunds
“Matraqa Tec” acts solely as a technical intermediary connecting users and independent service providers. In approved cases of cancellation or dispute resolution, refunds will be credited to the user’s in-app wallet.
6.3 non-transferability
Refunded amounts cannot be withdrawn in cash, reversed to bank cards, or transferred outside the Platform; they may only be used for future services within the application.
6.4 Financial Review
All financial requests are subject to review and approval and may be rejected or adjusted in the event of suspected misuse or fraud.
6.5 Cancellation After Provider Movement
Users retain the right to cancel without incurring charges prior to the service provider's movement. Once the movement has begun, cancellations will no longer be eligible for a refund; the fees collected shall be regarded as compensation for time and transportation expenses.

7. Service Provider Obligations
Service providers registered on the Platform are required to comply with the following obligations:
7.1 Professional Standard
Provide services with due skill, care, and diligence, in accordance with recognized industry standards and best practices.
7.2 Compliance with Laws
Fully comply with all applicable laws, regulations, and governmental requirements within the Kingdom of Saudi Arabia, including obtaining and maintaining any necessary licenses, permits, or certifications.
7.3 Accuracy of Representation
Provide accurate, truthful, and up-to-date information regarding qualifications, experience, pricing, and scope of services, and refrain from any misleading or deceptive practices.
7.4 Service Commitment
Honor all confirmed bookings and agreements made through the Platform, and complete services within the agreed timeframe unless prevented by force majeure circumstances.
7.5 Professional Conduct
Maintain respectful, ethical, and professional behavior when interacting with users, including avoiding harassment, discrimination, or inappropriate conduct of any kind.
7.6 Quality Assurance
Ensure that all work delivered meets the agreed specifications and quality expectations, and promptly address any deficiencies or complaints raised by users.
7.7 Safety and Responsibility
Adhere to all applicable health and safety standards and take full responsibility for any damages, losses, or incidents arising from the performance of the service.
7.8 Confidentiality
Protect and maintain the confidentiality of all user information and data accessed during service delivery, and refrain from unauthorized use or disclosure.
7.9 Platform Integrity
Use the Platform exclusively for legitimate service transactions and refrain from engaging in off-platform arrangements, circumvention of fees, or manipulation of ratings and reviews.
7.10 Communication and Responsiveness
Maintain timely and clear communication with users through the Platform and respond promptly to service requests, updates, or complaints.
7.11 Liability for Work
Service providers bear full responsibility for the execution, quality, and outcomes of the services they provide, and agree to indemnify the Platform against any claims arising from their actions or omissions.

8. Prohibition of Off-Platform Transactions
8.1 Violations
Includes exchanging contact details to bypass the Platform, agreeing on external payments, or soliciting clients outside the Platform.
8.2 Actions Against Providers
The Platform may suspend accounts, withhold funds, or claim compensation for violations.
8.3 Platform Liability
The Platform is not liable for any transactions conducted outside it.
8.4 Acknowledgment
All parties acknowledge that the Platform is the official channel for service transactions.

9. Limitation of Liability
9.1 Nature of the Platform
The Application functions as a technical intermediary; the responsibility for service execution lies with the provider.
9.2 Liability Limitation
The Platform shall not be liable for any direct, indirect, incidental, consequential, or special damages arising out of or in connection with the use of, or inability to use, the services provided through the Platform, including but not limited to service delays, interruptions, errors, or failures, where such events are beyond the Platform’s reasonable control. This limitation applies regardless of the cause of action, whether based on contract, tort (including negligence), or otherwise, and even if the Platform has been advised of the possibility of such damages.

10. Intellectual Property
10.1 Platform Rights
All intellectual property rights belong to “Matraqa Tec,” including trademarks, designs, and software, and shall not be used without prior permission.
10.2 User Content
Users grant the Platform a non-exclusive license to utilize uploaded content for operational and promotional objectives.

11. Privacy and Data Protection
11.1 Location Data
Users agree to the collection and use of location data for service delivery.
11.2 Confidentiality
Service providers are required to uphold the confidentiality of user data.
11.3 Data Rights
Users retain rights to access, correct, or delete their personal data, subject to applicable laws.

12. Amendments and Account Suspension
12.1 Amendments
The Platform may modify these terms at any time.
12.2 Continued Use
Continued use constitutes acceptance of updated terms.
12.3 Account Termination
Accounts may be suspended or terminated for violations.

13. Governing Law and Jurisdiction
These terms are governed by the laws of the Kingdom of Saudi Arabia. Any disputes shall fall under the jurisdiction of the competent courts in Saudi Arabia.

14. Contact Information
For inquiries, complaints, or requests, users must contact the Platform through its official communication channels.",
            'target_group' => 'all',
            'status' => 'active',
        ]);
    }
}
