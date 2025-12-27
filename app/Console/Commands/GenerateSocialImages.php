<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSocialImages extends Command
{
    protected $signature = 'social:generate-images';
    protected $description = 'Generate optimized social media sharing images';

    public function handle()
    {
        $source = storage_path('app/public/vilagmuhely-title.jpg');

        if (!file_exists($source)) {
            $this->error('Source image not found: '.$source);

            return 1;
        }

        // Create directories
        if (! is_dir(public_path('images/og'))) {
            mkdir(public_path('images/og'), 0755, true);
        }
        if (! is_dir(public_path('images/twitter'))) {
            mkdir(public_path('images/twitter'), 0755, true);
        }

        $this->info('Generating social media images...');

        // Variant 1: Open Graph Large (1200x630)
        $this->generateImage($source, 1200, 630, 'images/og/vilagmuhely-og.jpg', 85);

        // Variant 2: Open Graph Square (1200x1200)
        $this->generateImage($source, 1200, 1200, 'images/og/vilagmuhely-og-square.jpg', 85);

        // Variant 3: Twitter Card Large (1200x628)
        $this->generateImage($source, 1200, 628, 'images/twitter/vilagmuhely-twitter.jpg', 85);

        // Variant 4: Twitter Card Square (800x800)
        $this->generateImage($source, 800, 800, 'images/twitter/vilagmuhely-twitter-square.jpg', 85);

        // Variant 5: High Resolution (2016x1058)
        $this->generateImage($source, 2016, 1058, 'images/og/vilagmuhely-og-hires.jpg', 90);

        $this->info('All social media images generated successfully!');

        return 0;
    }

    private function generateImage(string $source, int $width, int $height, string $destination, int $quality)
    {
        // Load source image
        $sourceImage = imagecreatefromjpeg($source);
        if (! $sourceImage) {
            $this->error("Failed to load source image: {$source}");

            return;
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        // Calculate crop dimensions to fit target aspect ratio
        $targetRatio = $width / $height;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            // Source is wider - crop width
            $cropHeight = $sourceHeight;
            $cropWidth = (int) ($sourceHeight * $targetRatio);
            $cropX = (int) (($sourceWidth - $cropWidth) / 2);
            $cropY = 0;
        } else {
            // Source is taller - crop height
            $cropWidth = $sourceWidth;
            $cropHeight = (int) ($sourceWidth / $targetRatio);
            $cropX = 0;
            $cropY = (int) (($sourceHeight - $cropHeight) / 2);
        }

        // Create destination image
        $destImage = imagecreatetruecolor($width, $height);

        // Resample (crop and resize)
        imagecopyresampled(
            $destImage,
            $sourceImage,
            0, 0,                    // dest x, y
            $cropX, $cropY,          // source x, y
            $width, $height,         // dest width, height
            $cropWidth, $cropHeight  // source width, height
        );

        // Save with quality
        $fullPath = public_path($destination);
        imagejpeg($destImage, $fullPath, $quality);

        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($destImage);

        // Get file size
        $fileSize = filesize($fullPath);
        $fileSizeKb = round($fileSize / 1024, 1);

        $this->line("✓ Generated: {$destination} ({$width}x{$height}, {$fileSizeKb}KB)");
    }
}
