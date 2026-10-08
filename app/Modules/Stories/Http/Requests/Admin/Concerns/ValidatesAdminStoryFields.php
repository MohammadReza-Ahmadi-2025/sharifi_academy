<?php

namespace App\Modules\Stories\Http\Requests\Admin\Concerns;

use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;
use App\Support\PhpIniSize;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

trait ValidatesAdminStoryFields
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->refineMediaUploadErrors($validator);
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function storyFieldRules(bool $requireMedia): array
    {
        $type = $this->input('type');
        $mediaMaxKb = $this->mediaMaxKilobytes($type);

        return [
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::enum(StoryType::class)],
            'content' => [
                Rule::requiredIf($type === StoryType::Text->value),
                'nullable',
                'string',
                'max:5000',
            ],
            'background_color' => ['nullable', 'string', 'max:20'],
            'visibility' => ['required', Rule::enum(StoryVisibility::class)],
            'display_starts_at' => ['required', 'date'],
            'display_ends_at' => ['nullable', 'date', 'after:display_starts_at'],
            'publish_now' => ['sometimes', 'boolean'],
            'thumbnail' => [
                'nullable',
                'image',
                'max:'.max(1, (int) config('stories.max_image_mb', 10)) * 1024,
            ],
            'media' => array_values(array_filter([
                Rule::requiredIf($requireMedia && in_array($type, [StoryType::Image->value, StoryType::Video->value], true)),
                'nullable',
                'file',
                'max:'.$mediaMaxKb,
                $type === StoryType::Video->value
                    ? 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/3gpp,video/mpeg,video/x-msvideo'
                    : null,
                $type === StoryType::Video->value ? 'mimes:mp4,webm,mov,m4v,avi,3gp,3gpp' : null,
                $type === StoryType::Image->value ? 'image' : null,
            ])),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mediaMaxKilobytes(?string $type): int
    {
        if ($type === StoryType::Video->value) {
            return max(1, (int) config('stories.max_video_mb', 5)) * 1024;
        }

        return max(1, (int) config('stories.max_image_mb', 10)) * 1024;
    }

    protected function storyFieldMessages(): array
    {
        $videoMb = (int) config('stories.max_video_mb', 5);
        $imageMb = (int) config('stories.max_image_mb', 10);

        return [
            'media.uploaded' => 'فایل به سرور نرسید (احتمالاً محدودیت upload_max_filesize در PHP). جزئیات در بالای فرم نمایش داده می‌شود.',
            'media.max' => "حجم فایل بیش از حد مجاز است (ویدیو حداکثر {$videoMb}MB، تصویر حداکثر {$imageMb}MB).",
            'media.mimetypes' => 'فرمت ویدیو پشتیبانی نمی‌شود. MP4، WebM یا MOV را امتحان کنید.',
            'media.mimes' => 'فرمت ویدیو پشتیبانی نمی‌شود. MP4، WebM یا MOV را امتحان کنید.',
            'display_starts_at.required' => 'تاریخ شروع نمایش الزامی است.',
            'display_ends_at.after' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
        ];
    }

    protected function prepareStoryFields(): void
    {
        if ($this->has('publish_now')) {
            $this->merge([
                'publish_now' => $this->boolean('publish_now'),
            ]);
        }

        if ($this->input('display_ends_at') === '') {
            $this->merge(['display_ends_at' => null]);
        }
    }

    protected function refineMediaUploadErrors(Validator $validator): void
    {
        $type = $this->input('type');

        if (! in_array($type, [StoryType::Image->value, StoryType::Video->value], true)) {
            return;
        }

        $uploadMax = ini_get('upload_max_filesize') ?: '2M';
        $postMax = ini_get('post_max_size') ?: '8M';
        $uploadMaxBytes = PhpIniSize::toBytes($uploadMax);
        $postMaxBytes = PhpIniSize::toBytes($postMax);
        $contentLength = (int) $this->server('CONTENT_LENGTH', 0);

        $file = $this->file('media');

        if ($file !== null && ! $file->isValid()) {
            $validator->errors()->forget('media');
            $validator->errors()->add('media', $this->phpUploadErrorMessage($file->getError(), $uploadMax, $postMax, $contentLength));

            return;
        }

        if ($file === null && $validator->errors()->has('media')) {
            $validator->errors()->forget('media');
            $validator->errors()->add('media', $this->missingMediaMessage($uploadMax, $postMax, $contentLength));
        }
    }

    protected function missingMediaMessage(string $uploadMax, string $postMax, int $contentLength): string
    {
        $parts = ['فایل رسانه به سرور نرسید.'];

        if ($contentLength > 0) {
            $parts[] = 'حجم درخواست تقریبی: '.PhpIniSize::format($contentLength).'.';
        }

        $parts[] = "محدودیت‌های فعلی PHP: upload_max_filesize={$uploadMax}، post_max_size={$postMax}.";

        $uploadMaxBytes = PhpIniSize::toBytes($uploadMax);

        if ($contentLength > $uploadMaxBytes || $contentLength > PhpIniSize::toBytes($postMax)) {
            $parts[] = 'برای ویدیوهای چند مگابایتی باید upload_max_filesize و post_max_size را در php.ini افزایش دهید (مثلاً 128M). در لوکال می‌توانید با `php -c php.ini artisan serve` سرور را اجرا کنید.';
        } else {
            $parts[] = 'فایل را دوباره انتخاب کنید یا فرمت MP4/WebM را امتحان کنید.';
        }

        return implode(' ', $parts);
    }

    protected function phpUploadErrorMessage(int $errorCode, string $uploadMax, string $postMax, int $contentLength): string
    {
        $detail = match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "حجم فایل از سقف PHP بیشتر است (upload_max_filesize={$uploadMax}، post_max_size={$postMax}).",
            UPLOAD_ERR_PARTIAL => 'آپلود ناقص بود؛ دوباره تلاش کنید.',
            UPLOAD_ERR_NO_FILE => 'فایلی انتخاب نشده است.',
            default => 'خطای ناشناخته هنگام آپلود فایل.',
        };

        if ($contentLength > 0) {
            $detail .= ' حجم درخواست: '.PhpIniSize::format($contentLength).'.';
        }

        return $detail;
    }
}
