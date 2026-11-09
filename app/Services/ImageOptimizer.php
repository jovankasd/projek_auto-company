<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Optimalkan dan simpan gambar ke WebP (Quality 85, max dimension 2000px, strip EXIF).
     */
    public function optimizeAndStore(UploadedFile $file, string $directory = 'cars', ?string $filename = null): string
    {
        $filename = $filename ? Str::slug($filename) : Str::random(20);
        $targetFilename = $filename.'.webp';
        $storageDir = storage_path('app/public/'.trim($directory, '/'));

        if (! file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $targetPath = $storageDir.'/'.$targetFilename;

        // Cek apakah ekstensi GD aktif
        if (extension_loaded('gd')) {
            $this->processWithGd($file->getRealPath(), $targetPath, 2000, 85);
        } else {
            // Fallback aman jika GD belum diaktifkan di php.ini
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'webp') {
                copy($file->getRealPath(), $targetPath);
            } else {
                $targetFilename = $filename.'.'.$extension;
                $targetPath = $storageDir.'/'.$targetFilename;
                copy($file->getRealPath(), $targetPath);
            }
        }

        return 'storage/'.trim($directory, '/').'/'.$targetFilename;
    }

    /**
     * Buat thumbnail dengan ukuran spesifik.
     */
    public function createThumbnail(string $sourcePath, string $targetPath, int $width = 400, int $quality = 85): bool
    {
        if (! extension_loaded('gd') || ! file_exists($sourcePath)) {
            return false;
        }

        return $this->processWithGd($sourcePath, $targetPath, $width, $quality);
    }

    /**
     * Memproses gambar dengan GD: resize max dimension, strip EXIF, convert WebP.
     */
    protected function processWithGd(string $sourcePath, string $targetPath, int $maxDimension = 2000, int $quality = 85): bool
    {
        $content = file_get_contents($sourcePath);
        if ($content === false) {
            return false;
        }

        $image = @imagecreatefromstring($content);
        if (! $image) {
            return false;
        }

        // Pertahankan transparansi PNG / WebP
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width >= $height) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($height / $width) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($width / $height) * $maxDimension);
            }

            $resized = imagescale($image, $newWidth, $newHeight);
            if ($resized !== false) {
                imagedestroy($image);
                $image = $resized;
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }
        }

        $success = imagewebp($image, $targetPath, $quality);
        imagedestroy($image);

        return $success;
    }
}
