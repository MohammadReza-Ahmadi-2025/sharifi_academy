<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Http\Requests\Admin\Concerns\NormalizesCourseInput;
use App\Modules\Courses\Http\Requests\Admin\Concerns\NormalizesCourseSeoInput;
use App\Modules\Files\Http\Requests\Concerns\ValidatesGalleryUploads;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    use NormalizesCourseInput;
    use NormalizesCourseSeoInput;
    use ValidatesGalleryUploads;

    public function authorize(): bool
    {
        return $this->user()?->can('courses-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'code' => ['nullable', 'string', 'max:64', 'unique:courses,code'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'level' => ['nullable', Rule::enum(CourseLevel::class)],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(CourseStatus::class)],
            'is_featured' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('type', Category::TYPE_COURSE),
            ],
            ...$this->seoRules(),
            ...$this->galleryImageRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->assertMinimumOneImage($validator, 'gallery');
    }
}
