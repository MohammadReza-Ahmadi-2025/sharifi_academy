<?php

namespace App\Support\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Stores uploads as WebP under 2MB. Non-image files are stored unchanged.
 */
class OptimizedImageStore
{
    public const MAX_BYTES = 2 * 1024 * 1024;

    private const MAX_EDGE = 2560;

    private const START_QUALITY = 85;

    private const MIN_QUALITY = 40;

    /**
     * @return array{path: string, size: int, extension: string, name: string}
     */
    public function store(UploadedFile $upload, string $directory, string $disk = 'public'): array
    {
        if (! $this->isRasterImage($upload)) {
            $path = $upload->store(trim($directory, '/'), $disk);

            return [
                'path' => $path,
                'size' => (int) ($upload->getSize() ?: Storage::disk($disk)->size($path)),
                'extension' => strtolower((string) $upload->getClientOriginalExtension()),
                'name' => $upload->getClientOriginalName(),
            ];
        }

        $binary = $this->encodeUnderLimit($upload);
        $base = Str::ulid()->toBase32();
        $path = trim($directory, '/').'/'.$base.'.webp';

        if (! Storage::disk($disk)->put($path, $binary)) {
            throw new RuntimeException('Failed to store optimized image.');
        }

        $originalBase = pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME);

        return [
            'path' => $path,
            'size' => strlen($binary),
            'extension' => 'webp',
            'name' => ($originalBase !== '' ? $originalBase : $base).'.webp',
        ];
    }

    /**
     * Write an optimized WebP to a temp path for libraries like Spatie Media Library.
     */
    public function writeTemporaryPath(UploadedFile $upload): string
    {
        if (! $this->isRasterImage($upload)) {
            $source = $upload->getRealPath();

            if (! is_string($source) || $source === '' || ! is_file($source)) {
                throw new RuntimeException('Upload file is missing.');
            }

            $target = sys_get_temp_dir().'/'.Str::ulid()->toBase32().'.'.$upload->getClientOriginalExtension();
            if (! copy($source, $target)) {
                throw new RuntimeException('Failed to copy upload to temporary path.');
            }

            return $target;
        }

        $binary = $this->encodeUnderLimit($upload);
        $target = sys_get_temp_dir().'/'.Str::ulid()->toBase32().'.webp';
        if (file_put_contents($target, $binary) === false) {
            throw new RuntimeException('Failed to write temporary WebP.');
        }

        return $target;
    }

    public function isRasterImage(UploadedFile $upload): bool
    {
        $mime = strtolower((string) ($upload->getMimeType() ?: ''));

        if (str_starts_with($mime, 'image/') && ! in_array($mime, ['image/svg+xml', 'image/svg'], true)) {
            return true;
        }

        $extension = strtolower((string) $upload->getClientOriginalExtension());

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
    }

    private function encodeUnderLimit(UploadedFile $upload): string
    {
        $path = $upload->getRealPath();
        if (! is_string($path) || $path === '' || ! is_file($path)) {
            throw new RuntimeException('Upload file is missing.');
        }

        $contents = file_get_contents($path);
        if ($contents === false || $contents === '') {
            throw new RuntimeException('Upload file could not be read.');
        }

        try {
            $image = @imagecreatefromstring($contents);
        } catch (Throwable) {
            $image = false;
        }

        if ($image === false) {
            throw new RuntimeException('Unsupported or corrupt image upload.');
        }

        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($image);
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = 1.0;

        if ($width > self::MAX_EDGE || $height > self::MAX_EDGE) {
            $scale = min(self::MAX_EDGE / max(1, $width), self::MAX_EDGE / max(1, $height));
        }

        $working = $this->scaleImage($image, $scale);
        if ($working !== $image) {
            imagedestroy($image);
            $image = $working;
        }

        $binary = $this->encodeWebp($image, self::START_QUALITY);

        for ($quality = self::START_QUALITY; $quality >= self::MIN_QUALITY && strlen($binary) > self::MAX_BYTES; $quality -= 8) {
            $binary = $this->encodeWebp($image, $quality);
        }

        while (strlen($binary) > self::MAX_BYTES) {
            $width = imagesx($image);
            $height = imagesy($image);

            if ($width < 320 || $height < 320) {
                break;
            }

            $scaled = $this->scaleImage($image, 0.82);
            imagedestroy($image);
            $image = $scaled;
            $binary = $this->encodeWebp($image, self::MIN_QUALITY);
        }

        imagedestroy($image);

        if ($binary === '' || strlen($binary) > self::MAX_BYTES) {
            throw new RuntimeException('Could not compress image under 2MB.');
        }

        return $binary;
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function scaleImage(mixed $image, float $scale): mixed
    {
        if ($scale >= 0.999) {
            return $image;
        }

        $width = max(1, (int) round(imagesx($image) * $scale));
        $height = max(1, (int) round(imagesy($image) * $scale));
        $resized = imagecreatetruecolor($width, $height);

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $width, $height, $transparent);
        imagealphablending($resized, true);

        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $width,
            $height,
            imagesx($image),
            imagesy($image),
        );

        return $resized;
    }

    /**
     * @param  \GdImage  $image
     */
    private function encodeWebp(mixed $image, int $quality): string
    {
        ob_start();
        if (! imagewebp($image, null, max(0, min(100, $quality)))) {
            ob_end_clean();
            throw new RuntimeException('WebP encoding failed.');
        }

        $binary = ob_get_clean();

        if (! is_string($binary) || $binary === '') {
            throw new RuntimeException('WebP encoding produced empty output.');
        }

        return $binary;
    }
}
