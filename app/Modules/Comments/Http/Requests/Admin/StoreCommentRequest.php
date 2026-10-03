<?php

namespace App\Modules\Comments\Http\Requests\Admin;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Product;
use App\Modules\Comments\Enums\CommentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('comments-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'commentable_type' => ['required', 'string', Rule::in(['product', 'post'])],
            'commentable_id' => ['required', 'integer', 'min:1'],
            'display_name' => ['required', 'string', 'max:191'],
            'body' => ['required', 'string', 'max:2000'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'status' => ['required', 'integer', Rule::in(array_column(CommentStatus::cases(), 'value'))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $type = $this->input('commentable_type');
            $id = (int) $this->input('commentable_id');

            $exists = match ($type) {
                'product' => Product::query()->whereKey($id)->exists(),
                'post' => Post::query()->whereKey($id)->exists(),
                default => false,
            };

            if (! $exists) {
                $validator->errors()->add('commentable_id', 'منبع نظر معتبر نیست.');
            }
        });
    }
}
