<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageService
{
    /**
     * Upload image with optimization
     */
    public function uploadImage(UploadedFile $image, string $folder = 'images', array $options = []): string
    {
        $defaultOptions = [
            'max_width' => 1200, 'max_height' => 1200, 'quality' => 85, 'format' => 'jpg',
            'generate_thumbnails' => true, 'thumbnail_sizes' => [150, 300, 600]
        ];
        $options = array_merge($defaultOptions, $options);
        
        $fileName = $this->generateFileName($image);
        $originalPath = $image->storeAs($folder, $fileName, 'public');
        
        // Tạo thumbnails trước khi optimize
        if ($options['generate_thumbnails']) {
            $this->generateThumbnails($originalPath, $options['thumbnail_sizes']);
        }
        
        // Sau đó mới optimize file gốc
        $this->optimizeImage($originalPath, $options);
        
        return $originalPath;
    }

    /**
     * Upload avatar with specific settings
     */
    public function uploadAvatar(UploadedFile $image): string
    {
        return $this->uploadImage($image, 'avatars', [
            'max_width' => 400,
            'max_height' => 400,
            'quality' => 90,
            'format' => 'jpg',
            'generate_thumbnails' => true,
            'thumbnail_sizes' => [50, 100, 200]
        ]);
    }

    /**
     * Upload product image with specific settings
     */
    public function uploadProductImage(UploadedFile $image): string
    {
        return $this->uploadImage($image, 'products', [
            'max_width' => 800,
            'max_height' => 800,
            'quality' => 85,
            'format' => 'jpg',
            'generate_thumbnails' => true,
            'thumbnail_sizes' => [150, 300, 600]
        ]);
    }

    /**
     * Upload category image with specific settings
     */
    public function uploadCategoryImage(UploadedFile $image): string
    {
        return $this->uploadImage($image, 'categories', [
            'max_width' => 600,
            'max_height' => 600,
            'quality' => 85,
            'format' => 'jpg',
            'generate_thumbnails' => true,
            'thumbnail_sizes' => [100, 200, 400]
        ]);
    }

    /**
     * Delete image and its thumbnails
     */
    public function deleteImage(string $imagePath): bool
    {
        try {
            // Delete main image
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Delete thumbnails
            $this->deleteThumbnails($imagePath);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error deleting image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete image and return default image path
     */
    public function deleteImageAndSetDefault(string $imagePath, string $type = 'avatar'): string
    {
        // Xóa ảnh cũ nếu tồn tại
        if ($imagePath && $imagePath !== $this->getDefaultImagePath($type)) {
            $this->deleteImage($imagePath);
        }
        
        // Trả về đường dẫn ảnh mặc định
        return $this->getDefaultImagePath($type);
    }

    /**
     * Get default image path based on type
     */
    public function getDefaultImagePath(string $type = 'avatar'): string
    {
        switch ($type) {
            case 'avatar':
                return 'images/default-avatar.svg';
            case 'product':
                return 'images/default-product.svg';
            case 'category':
                return 'images/default-category.svg';
            default:
                return 'images/placeholder.svg';
        }
    }


    /**
     * Generate optimized filename
     */
    private function generateFileName(UploadedFile $image): string
    {
        $extension = $image->getClientOriginalExtension();
        $name = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
        $timestamp = time();
        $random = Str::random(8);

        return "{$name}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Optimize and resize image
     */
    private function optimizeImage(string $imagePath, array $options): void
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            if (!file_exists($fullPath)) {
                return;
            }
            
            $image = Image::make($fullPath);
            
            // Chỉ resize nếu ảnh quá lớn
            if ($image->width() > $options['max_width'] || $image->height() > $options['max_height']) {
                $image->resize($options['max_width'], $options['max_height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Lưu ảnh đã optimize vào file gốc
            $image->save($fullPath, $options['quality']);
            
        } catch (\Exception $e) {
            \Log::error('Error optimizing image: ' . $e->getMessage());
        }
    }

    /**
     * Generate thumbnails
     */
    private function generateThumbnails(string $imagePath, array $sizes): void
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return;
            }

            $image = Image::make($fullPath);
            $pathInfo = pathinfo($imagePath);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];

            foreach ($sizes as $size) {
                $thumbnail = $image->resize($size, $size, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $thumbnailPath = $directory . '/' . $filename . "_{$size}x{$size}." . $extension;
                $thumbnail->save(Storage::disk('public')->path($thumbnailPath), 85);
            }

        } catch (\Exception $e) {
            \Log::error('Error generating thumbnails: ' . $e->getMessage());
        }
    }

    /**
     * Delete thumbnails
     */
    private function deleteThumbnails(string $imagePath): void
    {
        try {
            $pathInfo = pathinfo($imagePath);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];

            $files = Storage::disk('public')->files($directory);
            
            foreach ($files as $file) {
                if (Str::contains($file, $filename . '_') && Str::contains($file, 'x' . $extension)) {
                    Storage::disk('public')->delete($file);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error deleting thumbnails: ' . $e->getMessage());
        }
    }

}
