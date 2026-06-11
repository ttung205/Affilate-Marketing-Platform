<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class ProductPage extends Page
{
    /**
     * Lấy URL của trang tạo sản phẩm.
     */
    public function url(): string
    {
        return '/admin/products/create';
    }

    /**
     * Xác nhận trình duyệt đang ở đúng trang tạo sản phẩm.
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
            '@price' => 'input[name="price"]',
            '@stock' => 'input[name="stock"]',
            '@category' => 'select[name="category_id"]',
            '@image' => 'input[name="image"]',
            '@submit' => 'button[type="submit"]',
        ];
    }

    /**
     * Thực hiện tạo sản phẩm mới qua giao diện.
     */
    public function createProduct(Browser $browser, string $name, string $description, int $price, int $stock, string $categoryId = ''): void
    {
        $browser->type('@name', $name)
                ->type('@description', $description)
                ->clear('@price')
                ->type('@price', (string)$price)
                ->clear('@stock')
                ->type('@stock', (string)$stock);

        if ($categoryId) {
            $browser->select('@category', $categoryId);
        }

        $browser->click('@submit');
    }
}
