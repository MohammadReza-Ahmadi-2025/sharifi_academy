<?php

namespace App\Modules\Files\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\UploadedFile;

trait ValidatesGalleryUploads
{
    /**
     * @return array<string, mixed>
     */
    protected function galleryImageRules(string $key = 'gallery', int $maxItems = 20): array
    {
        return [
            $key => ['required', 'array', 'min:1', 'max:'.$maxItems],
            "{$key}.*.id" => ['nullable', 'integer', 'exists:files,id'],
            "{$key}.*.sort" => ['nullable', 'integer', 'min:0'],
            "{$key}.*.alt" => ['nullable', 'string', 'max:255'],
            "{$key}.*.title" => ['nullable', 'string', 'max:120'],
            "{$key}.*.file" => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function singleImageRules(string $key = 'image'): array
    {
        return [
            $key => ['required', 'array', 'min:1', 'max:1'],
            "{$key}.0.id" => ['nullable', 'integer', 'exists:files,id'],
            "{$key}.0.alt" => ['nullable', 'string', 'max:255'],
            "{$key}.0.title" => ['nullable', 'string', 'max:120'],
            "{$key}.0.file" => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'remove_'.$key => ['prohibited'],
        ];
    }

    protected function assertMinimumOneImage(Validator $validator, string $key = 'gallery'): void
    {
        $validator->after(function (Validator $validator) use ($key): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->boolean('remove_'.$key)) {
                $validator->errors()->add($key, 'حداقل یک تصویر الزامی است؛ نمی‌توانید تنها تصویر را حذف کنید.');

                return;
            }

            if (! $this->manifestHasAtLeastOneImage($key)) {
                $validator->errors()->add($key, 'حداقل یک تصویر الزامی است.');
            }
        });
    }

    protected function manifestHasAtLeastOneImage(string $key): bool
    {
        $manifest = $this->input($key, []);

        if (! is_array($manifest) || $manifest === []) {
            return false;
        }

        foreach (array_values($manifest) as $index => $row) {
            $upload = $this->file("{$key}.{$index}.file");

            if ($upload instanceof UploadedFile) {
                return true;
            }

            if (is_array($row) && ! empty($row['id'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, UploadedFile|null>
     */
    public function galleryUploadsByIndex(string $key = 'gallery'): array
    {
        $uploads = [];
        $manifest = $this->input($key, []);

        if (! is_array($manifest)) {
            return [];
        }

        foreach (array_keys($manifest) as $index) {
            $uploads[(int) $index] = $this->file("{$key}.{$index}.file");
        }

        return $uploads;
    }
}
