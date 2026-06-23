<?php

namespace Database\Seeders;

use App\Models\AnnouncementBar;
use App\Models\Collection;
use App\Models\HeroSlide;
use App\Models\HomepageSection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\ShippingSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@store.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        Setting::set('store_name', 'Umair Fabrics');
        Setting::set('store_phone', '0370-4896496');
        Setting::set('store_whatsapp', '923704896496');
        Setting::set('store_email', 'info@umairfabrics.com');
        Setting::set('store_website', 'https://umairfabrics.com');

        ShippingSetting::updateOrCreate(['id' => 1], [
            'name' => 'Pakistan Flat Rate',
            'flat_rate' => 300,
            'free_shipping_threshold' => null,
            'is_active' => true,
        ]);

        $phone = '0370-4896496';

        $payments = [
            ['code' => 'cod', 'name' => 'Cash on Delivery', 'instructions' => 'Pay when your order is delivered.', 'sort_order' => 1],
            ['code' => 'bank', 'name' => 'Bank Transfer', 'instructions' => "Transfer to:\nBank: (Update in admin)\nAccount: (Update in admin)\nName: Umair Fabrics\n\nSend payment screenshot on WhatsApp: {$phone}", 'sort_order' => 2],
            ['code' => 'jazzcash', 'name' => 'JazzCash', 'instructions' => "Send payment to JazzCash: {$phone}\nTitle: Your order number\n\nSend screenshot on WhatsApp.", 'sort_order' => 3],
            ['code' => 'easypaisa', 'name' => 'Easypaisa', 'instructions' => "Send payment to Easypaisa: {$phone}\n\nSend screenshot on WhatsApp.", 'sort_order' => 4],
        ];

        foreach ($payments as $payment) {
            PaymentMethod::updateOrCreate(['code' => $payment['code']], array_merge($payment, ['is_enabled' => true]));
        }

        AnnouncementBar::updateOrCreate(['id' => 1], [
            'message' => "📞 For inquiries, call us at {$phone}!",
            'is_active' => true,
            'sort_order' => 1,
        ]);

        AnnouncementBar::updateOrCreate(['id' => 2], [
            'message' => 'Standard shipping for only 300 PKR on all orders across Pakistan!',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $collections = [
            ['name' => 'New Arrivals', 'slug' => 'new-arrivals', 'show_on_homepage' => false, 'homepage_title' => 'New Arrivals', 'sort_order' => 1],
            ['name' => 'Saya Lawn 3Pc', 'slug' => 'saya-lawn-3pc', 'show_on_homepage' => false, 'homepage_title' => 'Saya Lawn 3Pc', 'sort_order' => 2],
            ['name' => 'Maria B Lawn', 'slug' => 'maria-b-lawn', 'show_on_homepage' => false, 'homepage_title' => 'Maria B Lawn', 'sort_order' => 3],
            ['name' => 'Mens Collection', 'slug' => 'mens-collection', 'show_on_homepage' => false, 'homepage_title' => 'Mens Collection', 'sort_order' => 4],
        ];

        foreach ($collections as $data) {
            Collection::updateOrCreate(['slug' => $data['slug']], $data);
        }

        HomepageSection::query()->update(['is_active' => false]);

        $headerMenu = Menu::updateOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
        MenuItem::where('menu_id', $headerMenu->id)->delete();

        $headerItems = [
            ['label' => 'Home', 'url' => '/', 'sort_order' => 1],
            ['label' => 'Shop', 'url' => '/products', 'sort_order' => 2],
            ['label' => 'Bundles', 'url' => '/bundles', 'sort_order' => 3],
            ['label' => 'About', 'url' => '/pages/about-us', 'sort_order' => 4],
            ['label' => 'Contact', 'url' => '/pages/contact', 'sort_order' => 5],
        ];

        foreach ($headerItems as $item) {
            MenuItem::create(array_merge($item, ['menu_id' => $headerMenu->id]));
        }

        $footerMenu = Menu::updateOrCreate(['location' => 'footer'], ['name' => 'Footer Menu']);
        MenuItem::where('menu_id', $footerMenu->id)->delete();

        $footerItems = [
            ['label' => 'All Products', 'url' => '/products', 'sort_order' => 1],
            ['label' => 'Bundles', 'url' => '/bundles', 'sort_order' => 2],
            ['label' => 'About Us', 'url' => '/pages/about-us', 'sort_order' => 3],
            ['label' => 'Contact', 'url' => '/pages/contact', 'sort_order' => 4],
        ];

        foreach ($footerItems as $item) {
            MenuItem::create(array_merge($item, ['menu_id' => $footerMenu->id]));
        }

        Page::updateOrCreate(['slug' => 'about-us'], [
            'title' => 'About Us',
            'body_html' => '<p><strong>Umair Fabrics</strong> offers premium Pakistani lawn, cotton and unstitched suits with quality fabrics delivered across Pakistan.</p><p>We focus on elegant fabrics, honest pricing, and reliable customer service.</p>',
            'is_published' => true,
            'meta_title' => 'About Us — Umair Fabrics',
        ]);

        Page::updateOrCreate(['slug' => 'contact'], [
            'title' => 'Contact Us',
            'body_html' => '<p>Call us: <strong>0370-4896496</strong></p><p>Email: <strong>info@umairfabrics.com</strong></p><p>Website: <strong>umairfabrics.com</strong></p><p>We are happy to help with orders, product codes, and bundle inquiries.</p>',
            'is_published' => true,
            'meta_title' => 'Contact — Umair Fabrics',
        ]);

        $slides = [
            ['title' => 'Premium Lawn Collection', 'subtitle' => 'Soft fabrics for elegant unstitched suits', 'button_text' => 'Shop Now', 'button_url' => '/products', 'sort_order' => 1],
            ['title' => 'Summer Suit Fabrics', 'subtitle' => 'Lightweight lawn & cotton — no models, pure fabric focus', 'button_text' => 'View Bundles', 'button_url' => '/bundles', 'sort_order' => 2],
            ['title' => 'Umair Fabrics', 'subtitle' => 'Quality Pakistani fabrics delivered nationwide', 'button_text' => 'Contact Us', 'button_url' => '/pages/contact', 'sort_order' => 3],
        ];

        foreach ($slides as $slide) {
            HeroSlide::updateOrCreate(
                ['title' => $slide['title']],
                array_merge($slide, ['is_active' => true])
            );
        }
    }
}
