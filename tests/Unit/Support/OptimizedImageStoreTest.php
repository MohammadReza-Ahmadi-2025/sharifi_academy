<?php

namespace Tests\Unit\Support;

use App\Support\Storage\OptimizedImageStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OptimizedImageStoreTest extends TestCase
{
    public function test_converts_png_upload_to_webp_under_two_megabytes(): void
    {
        Storage::fake('public');

        $source = sys_get_temp_dir().'/opt-img-'.uniqid('', true).'.png';
        $canvas = imagecreatetruecolor(1800, 1800);
        for ($y = 0; $y < 1800; $y += 20) {
            $color = imagecolorallocate($canvas, ($y * 3) % 255, 80, 160);
            imagefilledrectangle($canvas, 0, $y, 1799, $y + 19, $color);
        }
        imagepng($canvas, $source, 0);
        imagedestroy($canvas);

        $upload = new UploadedFile($source, 'photo.png', 'image/png', null, true);
        $stored = app(OptimizedImageStore::class)->store($upload, 'optimized-tests');

        @unlink($source);

        $this->assertSame('webp', $stored['extension']);
        $this->assertLessThanOrEqual(OptimizedImageStore::MAX_BYTES, $stored['size']);
        $this->assertTrue(str_ends_with($stored['path'], '.webp'));
        Storage::disk('public')->assertExists($stored['path']);
        $this->assertSame('image/webp', Storage::disk('public')->mimeType($stored['path']));
    }

    public function test_leaves_non_image_uploads_unchanged(): void
    {
        Storage::fake('public');

        $upload = UploadedFile::fake()->create('clip.mp4', 512, 'video/mp4');
        $stored = app(OptimizedImageStore::class)->store($upload, 'videos');

        $this->assertSame('mp4', $stored['extension']);
        $this->assertTrue(str_ends_with($stored['path'], '.mp4'));
        Storage::disk('public')->assertExists($stored['path']);
    }
}
