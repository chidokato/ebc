<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ImageResizer
{
    public const MAX_DIMENSION = 1900;

    public static function store(UploadedFile $file, string $relativeDirectory): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $relativePath = trim($relativeDirectory, '/') . '/' . Str::uuid() . '.' . $extension;
        $destination = base_path($relativePath);

        File::ensureDirectoryExists(dirname($destination));

        // SVG is resolution-independent and should remain untouched.
        if ($extension === 'svg') {
            $file->move(dirname($destination), basename($destination));

            return str_replace('\\', '/', $relativePath);
        }

        $size = @getimagesize($file->getRealPath());
        if (! $size) {
            throw new RuntimeException('Không thể đọc kích thước ảnh đã tải lên.');
        }

        [$width, $height] = $size;
        if (max($width, $height) <= self::MAX_DIMENSION) {
            $file->move(dirname($destination), basename($destination));

            return str_replace('\\', '/', $relativePath);
        }

        [$source, $writer] = self::imageHandler($file->getRealPath(), $extension);
        $scale = self::MAX_DIMENSION / max($width, $height);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($extension, ['png', 'webp'], true)) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefill($target, 0, 0, $transparent);
        }

        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        $writer($target, $destination);
        imagedestroy($source);
        imagedestroy($target);

        return str_replace('\\', '/', $relativePath);
    }

    private static function imageHandler(string $path, string $extension): array
    {
        return match ($extension) {
            'jpg' => [imagecreatefromjpeg($path), static fn ($image, $destination) => imagejpeg($image, $destination, 88)],
            'png' => [imagecreatefrompng($path), static fn ($image, $destination) => imagepng($image, $destination, 6)],
            'webp' => [imagecreatefromwebp($path), static fn ($image, $destination) => imagewebp($image, $destination, 88)],
            default => throw new RuntimeException('Định dạng ảnh không được hỗ trợ.'),
        };
    }
}
