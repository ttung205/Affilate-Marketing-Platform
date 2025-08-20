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

if (!function_exists('get_thumbnail_url')) {
    /**
     * Get thumbnail URL
     */
    function get_thumbnail_url(?string $imagePath, int $size = 150, ?string $fallback = null): string
    {
        if (!$imagePath) {
            return $fallback ?: asset('images/placeholder.svg');
        }

        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . "_{$size}x{$size}." . $pathInfo['extension'];

        if (Storage::disk('public')->exists($thumbnailPath)) {
            return asset('storage/' . $thumbnailPath);
        }

        // Fallback to original image if thumbnail doesn't exist
        return get_image_url($imagePath, $fallback);
    }
}

if (!function_exists('format_file_size')) {
    /**
     * Format file size in human readable format
     */
    function format_file_size(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
