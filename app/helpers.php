<?php

if (!function_exists('get_image_url')) {
    /**
     * Get image URL with fallback
     */
    function get_image_url(?string $imagePath, ?string $fallback = null): string
    {
        if (!$imagePath) {
            return $fallback ?: asset('images/placeholder.svg');
        }
        
        // Kiểm tra xem có phải URL từ Google/external không
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }
        
        // Kiểm tra xem có phải ảnh mặc định không
        if (str_starts_with($imagePath, 'images/default-')) {
            return asset($imagePath);
        }
        
        if (Storage::disk('public')->exists($imagePath)) {
            return asset('storage/' . $imagePath);
        }
        
        return $fallback ?: asset('images/placeholder.svg');
    }
}
