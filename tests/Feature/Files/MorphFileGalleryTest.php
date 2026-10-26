<?php

namespace Tests\Feature\Files;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Files\Services\MorphFileSyncService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MorphFileGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_single_image_and_product_course_galleries(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)->post('/admin/categories', [
            'type' => Category::TYPE_PRODUCT,
            'title' => 'دسته با تصویر',
            'status' => true,
            'image' => [
                0 => [
                    'alt' => 'دسته',
                    'file' => UploadedFile::fake()->image('category.jpg'),
                ],
            ],
        ])->assertRedirect();

        $category = Category::query()->where('title', 'دسته با تصویر')->firstOrFail();
        $this->assertSame(1, $category->image()->count());

        $this->actingAs($admin)->post('/admin/catalog/products', [
            'title' => 'محصول گالری',
            'status' => true,
            'is_visible' => true,
            'gallery' => [
                0 => [
                    'sort' => 1,
                    'file' => UploadedFile::fake()->image('p1.jpg'),
                ],
                1 => [
                    'sort' => 2,
                    'file' => UploadedFile::fake()->image('p2.jpg'),
                ],
            ],
        ])->assertRedirect();

        $product = Product::query()->where('title', 'محصول گالری')->firstOrFail();
        $this->assertSame(2, $product->gallery()->count());

        $courseCategory = Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'دسته دوره',
            'slug' => 'course-gallery-category',
            'status' => true,
        ]);

        $this->actingAs($admin)->post('/admin/courses', [
            'title' => 'دوره گالری',
            'status' => CourseStatus::Draft->value,
            'category_ids' => [$courseCategory->id],
            'gallery' => [
                0 => [
                    'file' => UploadedFile::fake()->image('c1.jpg'),
                ],
            ],
        ])->assertRedirect();

        $course = Course::query()->where('title', 'دوره گالری')->firstOrFail();
        $this->assertSame(1, $course->gallery()->count());
        $this->assertSame(MorphFileSyncService::TYPE_GALLERY, $course->gallery()->first()?->type);
    }

    public function test_removing_one_gallery_image_deletes_physical_file(): void
    {
        Storage::fake('public');
        $product = Product::query()->create([
            'title' => 'P',
            'slug' => 'p-del',
            'status' => true,
            'is_visible' => true,
        ]);

        $pathA = 'products/'.$product->id.'/a.jpg';
        $pathB = 'products/'.$product->id.'/b.jpg';
        Storage::disk('public')->put($pathA, 'a');
        Storage::disk('public')->put($pathB, 'b');

        $fileA = $product->files()->create([
            'name' => 'a.jpg',
            'path' => $pathA,
            'type' => MorphFileSyncService::TYPE_GALLERY,
            'sort' => 1,
            'status' => 1,
        ]);

        $fileB = $product->files()->create([
            'name' => 'b.jpg',
            'path' => $pathB,
            'type' => MorphFileSyncService::TYPE_GALLERY,
            'sort' => 2,
            'status' => 1,
        ]);

        app(MorphFileSyncService::class)->syncGallery($product, [
            ['id' => $fileB->id, 'sort' => 1],
        ], [], MorphFileSyncService::TYPE_GALLERY);

        $this->assertSame(1, $product->gallery()->count());
        $this->assertFalse(Storage::disk('public')->exists($pathA));
        $this->assertTrue(Storage::disk('public')->exists($pathB));
    }

    public function test_product_cannot_be_stored_without_gallery_image(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/catalog/products', [
                'title' => 'بدون عکس',
                'status' => true,
                'is_visible' => true,
            ])
            ->assertSessionHasErrors('gallery');
    }
}
