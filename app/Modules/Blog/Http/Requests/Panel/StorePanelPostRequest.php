<?php

namespace App\Modules\Blog\Http\Requests\Panel;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use App\Modules\Files\Http\Requests\Concerns\ValidatesGalleryUploads;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePanelPostRequest extends FormRequest
{
    use ValidatesGalleryUploads;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:50000'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('type', Category::TYPE_BLOG)),
            ],
            'status' => ['required', 'string', Rule::in(PostStatus::panelWritableValues())],
            ...$this->optionalSingleImageRules('featured'),
        ];
    }
}
