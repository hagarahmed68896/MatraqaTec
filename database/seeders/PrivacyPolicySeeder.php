<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrivacyPolicy;

class PrivacyPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing general privacy policies to avoid duplicates
        PrivacyPolicy::whereIn('target_group', ['all', 'both'])->delete();

        $policies = [
            [
                'title_ar' => '1. استخدام المنصة',
                'title_en' => '1. Platform Usage',
                'content_ar' => 'باستخدامك للمنصة، فإنك توافق على استخدام الخدمات بطريقة قانونية ومسؤولة ووفقاً للسياسات المعلنة. يُمنع استخدام المنصة بأي شكل يلحق الضرر بالخوادم أو بالأنظمة الأمنية المخصصة للمنصة أو بالبنية التحتية.',
                'content_en' => 'By using the platform, you agree to use the services in a legal and responsible manner in accordance with the declared policies. Misusing the platform in any way that damages the servers, security systems, or infrastructure is strictly prohibited.',
                'target_group' => 'all',
                'status' => 'active',
            ],
            [
                'title_ar' => '2. إنشاء الحساب',
                'title_en' => '2. Account Creation',
                'content_ar' => 'يتعين على المستخدم تقديم معلومات دقيقة وصحيحة ومحدثة عند التسجيل في المنصة. تقع على عاتق المستخدم كامل المسؤولية في الحفاظ على سرية معلومات حسابه وكلمة المرور، ويُمنع منعاً باتاً مشاركة بيانات الدخول مع أي طرف ثالث تحت أي ظرف من الظروف.',
                'content_en' => 'Users must provide accurate, truthful, and up-to-date information when registering on the platform. The user bears full responsibility for maintaining the confidentiality of their account details and password, and sharing login credentials with any third party under any circumstances is strictly prohibited.',
                'target_group' => 'all',
                'status' => 'active',
            ],
            [
                'title_ar' => '3. حقوق الملكية الفكرية',
                'title_en' => '3. Intellectual Property Rights',
                'content_ar' => 'جميع المحتويات والنصوص والتصاميم والشعارات والأيقونات والبرمجيات المتوافرة ضمن المنصة هي ملكية فكرية حصرية ومحمية بموجب حقوق النشر والعلامات التجارية المحلية والدولية. لا يحق لأي شخص نسخ أو استخدام هذه المحتويات دون إذن كتابي مسبق.',
                'content_en' => 'All content, texts, designs, logos, icons, and software available on the platform are the exclusive intellectual property of the platform and are protected by local and international copyright and trademark laws. No one has the right to copy or use this content without prior written permission.',
                'target_group' => 'all',
                'status' => 'active',
            ],
            [
                'title_ar' => '4. استخدام البيانات',
                'title_en' => '4. Data Usage',
                'content_ar' => 'نحتفظ بالحق في استخدام البيانات والمعلومات غير الشخصية التي يتم جمعها بطرق تلقائية أو التي يقدمها المستخدم بغرض تحسين جودة الخدمات، وتطوير المنتجات، وتخصيص التنبيهات واهتمامات المزايدة الفعالة مع الحفاظ الكامل على أمان البيانات الشخصية وتشفيرها.',
                'content_en' => 'We reserve the right to use non-personal data and information collected automatically or provided by the user for the purpose of improving service quality, developing products, and customizing alerts and active bidding interests, while fully maintaining the security and encryption of personal data.',
                'target_group' => 'all',
                'status' => 'active',
            ],
            [
                'title_ar' => '5. إنهاء الحساب',
                'title_en' => '5. Account Termination',
                'content_ar' => 'يحق لإدارة المنصة وبموجب تقديرها الخاص تعليق أو إنهاء حساب المستخدم أو حظر وصوله للمنصة والخدمات في حال ثبوت انتهاك بنود الشروط والأحكام أو سياسة الخصوصية، أو ممارسة المزايدات الوهمية والعشوائية، أو إساءة الاستخدام.',
                'content_en' => 'The platform management reserves the right, in its sole discretion, to suspend or terminate the user\'s account or block their access to the platform and services if a violation of the terms and conditions or privacy policy is proven, or in the case of fake or random bidding, or misuse.',
                'target_group' => 'all',
                'status' => 'active',
            ]
        ];

        foreach ($policies as $policy) {
            PrivacyPolicy::create($policy);
        }
    }
}
