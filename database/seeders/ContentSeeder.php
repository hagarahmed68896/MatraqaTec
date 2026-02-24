<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contents = [
            [
                'title_ar' => 'العروض الرئيسية',
                'title_en' => 'Main Banners',
                'is_visible' => true,
                'items' => [
                    [
                        'title_ar' => 'خصم 50% على السباكة',
                        'title_en' => '50% Off Plumbing',
                        'description_ar' => 'احصل على خصم لفترة محدودة على جميع خدمات السباكة المنزلية',
                        'description_en' => 'Get a limited time discount on all home plumbing services',
                        'button_text_ar' => 'اطلب الآن',
                        'button_text_en' => 'Order Now',
                    ],
                    [
                        'title_ar' => 'صيانة التكييف',
                        'title_en' => 'AC Maintenance',
                        'description_ar' => 'استعد للصيف بخصم خاص على فحص التكييف',
                        'description_en' => 'Get ready for summer with a special discount on AC inspection',
                        'button_text_ar' => 'احجز موعد',
                        'button_text_en' => 'Book Now',
                    ],
                ]
            ],
            [
                'title_ar' => 'عروض حصرية',
                'title_en' => 'Exclusive Offers',
                'is_visible' => true,
                'items' => [
                    [
                        'title_ar' => 'باقة المنزل الكاملة',
                        'title_en' => 'Full House Package',
                        'description_ar' => 'فحص شامل للكهرباء والسباكة بأسعار منافسة',
                        'description_en' => 'Comprehensive electrical and plumbing inspection at competitive prices',
                        'button_text_ar' => 'تفاصيل الباقة',
                        'button_text_en' => 'Package Details',
                    ],
                ]
            ],
            [
                'title_ar' => 'لماذا مطرقة تيك؟',
                'title_en' => 'Why MatraqaTec?',
                'is_visible' => true,
                'items' => [
                    [
                        'title_ar' => 'فنيون محترفون',
                        'title_en' => 'Professional Technicians',
                        'description_ar' => 'نخبة من أفضل الفنيين المدربين لخدمتك',
                        'description_en' => 'The best trained technicians to serve you',
                        'button_text_ar' => 'اعرف أكثر',
                        'button_text_en' => 'Learn More',
                    ],
                    [
                        'title_ar' => 'ضمان الجودة',
                        'title_en' => 'Quality Guarantee',
                        'description_ar' => 'نضمن لك جودة العمل ورضاك التام',
                        'description_en' => 'We guarantee quality work and your total satisfaction',
                        'button_text_ar' => 'سياسة الضمان',
                        'button_text_en' => 'Guarantee Policy',
                    ],
                ]
            ],
        ];

        foreach ($contents as $cData) {
            $items = $cData['items'];
            unset($cData['items']);
            
            $content = Content::create($cData);
            
            foreach ($items as $iData) {
                $content->items()->create($iData);
            }
        }
    }
}
