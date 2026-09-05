<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Menu;
use App\Models\Slider;
use App\Models\HomepageSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@ebc.local')],
            [
                'name' => env('ADMIN_NAME', 'EBC Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!')),
                'is_admin' => true,
            ]
        );

        $menus = [
            'vi' => [['Giới thiệu', '#about'], ['Hệ thống sảnh', '#ballroom'], ['Thực đơn tiệc', '#menu'], ['Tin tức & Sự kiện', '#news'], ['Đăng ký tư vấn', '#consultation']],
            'en' => [['About us', '#about'], ['Event spaces', '#ballroom'], ['Banquet menu', '#menu'], ['News & Events', '#news'], ['Request a consultation', '#consultation']],
            'zh' => [['关于我们', '#about'], ['活动场地', '#ballroom'], ['宴会菜单', '#menu'], ['新闻与活动', '#news'], ['预约咨询', '#consultation']],
            'ko' => [['소개', '#about'], ['행사 공간', '#ballroom'], ['연회 메뉴', '#menu'], ['뉴스 및 이벤트', '#news'], ['상담 신청', '#consultation']],
        ];

        foreach ($menus as $locale => $items) {
            foreach ($items as $order => [$label, $url]) {
                Menu::firstOrCreate(
                    ['locale' => $locale, 'label' => $label, 'location' => 'header'],
                    ['url' => $url, 'sort_order' => $order + 1, 'is_active' => true]
                );
            }
        }

        $slides = [
            'vi' => [['Elite Business Center', 'Không gian sự kiện sáng tạo cho doanh nghiệp', 'Đăng ký tư vấn'], ['Không gian hội nghị đẳng cấp', 'Nơi mỗi sự kiện trở thành một dấu ấn đáng nhớ', 'Khám phá ngay']],
            'en' => [['Elite Business Center', 'Creative event spaces for businesses', 'Request a consultation'], ['Premium meeting spaces', 'Where every event becomes a memorable milestone', 'Explore now']],
            'zh' => [['Elite Business Center', '为企业打造的创意活动空间', '预约咨询'], ['高端会议空间', '让每一场活动成为难忘印记', '立即探索']],
            'ko' => [['Elite Business Center', '기업을 위한 창의적인 이벤트 공간', '상담 신청'], ['프리미엄 미팅 공간', '모든 행사를 기억에 남는 순간으로', '자세히 보기']],
        ];

        foreach ($slides as $locale => $items) {
            foreach ($items as $order => [$title, $description, $buttonLabel]) {
                Slider::firstOrCreate(
                    ['locale' => $locale, 'title' => $title],
                    [
                        'description' => $description,
                        'image_path' => $order === 0 ? 'frontend/images/capital.jpg' : 'frontend/images/ebc-event-hall.png',
                        'button_label' => $buttonLabel,
                        'button_url' => '#consultation',
                        'sort_order' => $order + 1,
                        'is_active' => true,
                    ]
                );
            }
        }

        $homepage = [
            'vi' => [
                ['hero', 'Hero banner', 'Khu vực banner chính và lời kêu gọi hành động', ['hero-title' => 'Nội dung hero', 'hero-cta' => 'Nút đăng ký tư vấn']],
                ['about', 'Giới thiệu EBC', 'Các lợi thế và giải pháp sự kiện', ['benefits' => 'Lợi thế nổi bật']],
                ['ballroom', 'Hệ thống sảnh', 'Các không gian sự kiện tại Elite Business Center', ['elite-ballroom' => 'Elite Ballroom', 'elite-suite' => 'Elite Suite', 'flex-suite' => 'Flex Suite']],
                ['services', 'Dịch vụ và tiện ích', 'Dịch vụ sự kiện, ẩm thực và tiện ích', ['event-services' => 'Dịch vụ sự kiện', 'amenities' => 'Tiện ích']],
                ['consultation', 'Tư vấn', 'Biểu mẫu đăng ký tư vấn', ['contact-form' => 'Thông tin liên hệ']],
                ['news', 'Tin tức', 'Danh sách tin tức và sự kiện mới nhất', []],
            ],
            'en' => [['hero', 'Hero banner', null, ['hero-title' => 'Hero content']], ['about', 'About EBC', null, ['benefits' => 'Key benefits']], ['ballroom', 'Event spaces', null, ['elite-ballroom' => 'Elite Ballroom', 'elite-suite' => 'Elite Suite', 'flex-suite' => 'Flex Suite']], ['services', 'Services & amenities', null, []], ['consultation', 'Consultation', null, []], ['news', 'News', null, []]],
            'zh' => [['hero', '首页横幅', null, ['hero-title' => '横幅内容']], ['about', '关于 EBC', null, ['benefits' => '核心优势']], ['ballroom', '活动场地', null, ['elite-ballroom' => 'Elite Ballroom', 'elite-suite' => 'Elite Suite', 'flex-suite' => 'Flex Suite']], ['services', '服务与设施', null, []], ['consultation', '咨询', null, []], ['news', '新闻', null, []]],
            'ko' => [['hero', '메인 배너', null, ['hero-title' => '배너 콘텐츠']], ['about', 'EBC 소개', null, ['benefits' => '주요 장점']], ['ballroom', '행사 공간', null, ['elite-ballroom' => 'Elite Ballroom', 'elite-suite' => 'Elite Suite', 'flex-suite' => 'Flex Suite']], ['services', '서비스 및 편의시설', null, []], ['consultation', '상담', null, []], ['news', '뉴스', null, []]],
        ];

        foreach ($homepage as $locale => $sections) {
            foreach ($sections as $order => [$key, $title, $content, $children]) {
                $parent = HomepageSection::firstOrCreate(
                    ['locale' => $locale, 'key' => $key, 'parent_id' => null],
                    ['title' => $title, 'content' => $content, 'sort_order' => $order + 1, 'is_active' => true]
                );
                foreach ($children as $childOrder => $childTitle) {
                    HomepageSection::firstOrCreate(
                        ['locale' => $locale, 'key' => $childOrder, 'parent_id' => $parent->id],
                        ['title' => $childTitle, 'sort_order' => array_search($childOrder, array_keys($children), true) + 1, 'is_active' => true]
                    );
                }
            }
        }
    }
}
