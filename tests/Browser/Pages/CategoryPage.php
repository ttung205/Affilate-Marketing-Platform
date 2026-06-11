<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class CategoryPage extends Page
{
    /**
     * Lấy URL của trang tạo danh mục.
     */
    public function url(): string
    {
        return '/admin/categories/create';
    }

    /**
     * Xác nhận trình duyệt đang ở đúng trang tạo danh mục.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Định nghĩa các selector thu gọn cho trang.
     */
    public function elements(): array
    {
        return [
            '@name' => 'input[name="name"]',
            '@description' => 'textarea[name="description"]',
            '@sort_order' => 'input[name="sort_order"]',
            '@image' => 'input[name="image"]',
            '@submit' => 'button[type="submit"]',
        ];
    }

    /**
     * Thực hiện tạo danh mục mới qua giao diện.
     */
    public function createCategory(Browser $browser, string $name, string $description, int $sortOrder = 0): void
    {
        $browser->type('@name', $name)
                ->type('@description', $description)
                ->clear('@sort_order')
                ->type('@sort_order', (string)$sortOrder)
                ->click('@submit');
    }
}
