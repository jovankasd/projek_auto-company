<?php

namespace App\Console\Commands;

use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeMediaCommand extends Command
{
    protected $signature = 'media:optimize {directory=public/storage : Direktori gambar}';

    protected $description = 'Optimasi seluruh media gambar ke format WebP berkualitas tinggi';

    public function handle(ImageOptimizer $optimizer): int
    {
        $this->info('Memulai optimasi media...');

        $baseDir = storage_path('app/public');
        if (! File::exists($baseDir)) {
            $this->warn('Direktori storage/app/public tidak ditemukan.');

            return self::SUCCESS;
        }

        $files = File::allFiles($baseDir);
        $count = 0;

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
                $target = $file->getPath().'/'.pathinfo($file->getFilename(), PATHINFO_FILENAME).'.webp';
                if (! File::exists($target)) {
                    $optimizer->createThumbnail($file->getRealPath(), $target, 2000, 85);
                    $count++;
                    $this->line("Optimasi: {$file->getFilename()} -> WebP");
                }
            }
        }

        $this->info("Selesai! {$count} gambar berhasil dioptimasi.");

        return self::SUCCESS;
    }
}
