<?php

namespace App\Modules\Home\Http\Requests\Admin\Concerns;

use App\Modules\Home\Support\AdminHomeEnumOptions;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait HomeSectionTabValidation
{
    /**
     * @return array<string, mixed>
     */
    protected function homeSectionTabRules(): array
    {
        $contentTypes = array_keys(AdminHomeEnumOptions::contentTypes());
        $sourceTypes = array_keys(AdminHomeEnumOptions::sourceTypes());

        return [
            'tabs' => ['required', 'array', 'min:1'],
            'tabs.*.id' => ['nullable', 'integer', 'exists:home_section_tabs,id'],
            'tabs.*.title' => ['nullable', 'string', 'max:255'],
            'tabs.*.content_type' => ['required', Rule::in($contentTypes)],
            'tabs.*.source_type' => ['required', Rule::in($sourceTypes)],
            'tabs.*.limit' => ['nullable', 'integer', 'min:0', 'max:50'],
            'tabs.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'tabs.*.is_active' => ['nullable', 'boolean'],
            'tabs.*.settings' => ['nullable', 'array'],
            'tabs.*.settings.title_url' => ['nullable', 'string', 'max:500'],
            'tabs.*.settings.view_all_url' => ['nullable', 'string', 'max:500'],
            'tabs.*.settings.category_id' => ['nullable', 'integer'],
            'tabs.*.settings.brand_id' => ['nullable', 'integer'],
            'tabs.*.settings.features' => ['nullable', 'array', 'max:3'],
            'tabs.*.settings.features.*.label' => ['nullable', 'string', 'max:255'],
            'tabs.*.settings.features.*.text' => ['nullable', 'string', 'max:500'],
            'tabs.*.settings.features.*.image_url' => ['nullable', 'string', 'max:500'],
            'tabs.*.settings.features.*.image_alt' => ['nullable', 'string', 'max:255'],
            'tabs.*.settings.features.*.image_title' => ['nullable', 'string', 'max:255'],
            'tabs.*.settings.features.*.image' => ['nullable', 'array'],
            'tabs.*.settings.features.*.image.0.file' => ['nullable', 'image', 'max:5120'],
            'tabs.*.settings.features.*.image.0.alt' => ['nullable', 'string', 'max:255'],
            'tabs.*.settings.features.*.image.0.title' => ['nullable', 'string', 'max:255'],
            'tabs.*.settings.testimonials' => ['nullable', 'array', 'max:12'],
            'tabs.*.settings.testimonials.*.name' => ['nullable', 'string', 'max:120'],
            'tabs.*.settings.testimonials.*.role' => ['nullable', 'string', 'max:160'],
            'tabs.*.settings.testimonials.*.quote' => ['nullable', 'string', 'max:1200'],
            'tabs.*.settings.testimonials.*.rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'tabs.*.manual_item_ids' => ['nullable', 'array'],
            'tabs.*.manual_item_ids.*' => ['integer', 'min:1'],
            'tabs.*.banners' => ['nullable', 'array'],
            'tabs.*.banners.*.id' => ['nullable', 'integer', 'exists:banners,id'],
            'tabs.*.banners.*.title' => ['nullable', 'string', 'max:255'],
            'tabs.*.banners.*.subtitle' => ['nullable', 'string', 'max:255'],
            'tabs.*.banners.*.button_text' => ['nullable', 'string', 'max:100'],
            'tabs.*.banners.*.button_link' => ['nullable', 'string', 'max:500'],
            'tabs.*.banners.*.starts_at' => ['nullable', 'date'],
            'tabs.*.banners.*.ends_at' => ['nullable', 'date'],
            'tabs.*.banners.*.image' => ['nullable', 'array'],
            'tabs.*.banners.*.image.*.id' => ['nullable', 'integer', 'exists:files,id'],
            'tabs.*.banners.*.image.*.file' => ['nullable', 'image', 'max:5120'],
        ];
    }

    protected function validateHomeSectionTabs(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('tabs', []) as $index => $tab) {
                if (! ($tab['is_active'] ?? true)) {
                    continue;
                }

                $contentType = $tab['content_type'] ?? '';
                $sourceType = $tab['source_type'] ?? '';

                if ($contentType === 'banner') {
                    if ($sourceType !== 'manual') {
                        $validator->errors()->add(
                            "tabs.$index.source_type",
                            'برای این نوع، روش نمایش باید «انتخاب دستی» باشد.',
                        );
                    }

                    $banners = $tab['banners'] ?? [];
                    if ($banners === []) {
                        $validator->errors()->add(
                            "tabs.$index.banners",
                            'حداقل یک اسلاید بنر با تصویر اضافه کنید.',
                        );
                    }

                    continue;
                }

                if ($contentType === 'testimonials') {
                    if ($sourceType !== 'section') {
                        $validator->errors()->add(
                            "tabs.$index.source_type",
                            'برای نظر کاربران، روش نمایش باید «تعریف در تنظیمات سکشن» باشد.',
                        );
                    }

                    $rows = $tab['settings']['testimonials'] ?? [];
                    $validCount = 0;
                    if (is_array($rows)) {
                        foreach ($rows as $row) {
                            if (! is_array($row)) {
                                continue;
                            }
                            if (filled($row['name'] ?? null) && filled($row['quote'] ?? null)) {
                                $validCount++;
                            }
                        }
                    }

                    if ($validCount < 1) {
                        $validator->errors()->add(
                            "tabs.$index.settings.testimonials",
                            'حداقل یک نظر با نام و متن کامل وارد کنید.',
                        );
                    }

                    continue;
                }

                if ($contentType === 'features') {
                    if ($sourceType !== 'section') {
                        $validator->errors()->add(
                            "tabs.$index.source_type",
                            'برای گرید سه‌تایی، روش نمایش باید «تعریف در تنظیمات سکشن» باشد.',
                        );
                    }

                    $grid = $tab['settings']['features'] ?? [];
                    if (is_array($grid) && count($grid) > 3) {
                        $validator->errors()->add(
                            "tabs.$index.settings.features",
                            'در هر تب گرید سه‌تایی حداکثر ۳ ستون مجاز است.',
                        );
                    }

                    $hasImage = false;
                    if (is_array($grid)) {
                        foreach (array_values($grid) as $featureIndex => $feature) {
                            if (! is_array($feature)) {
                                continue;
                            }

                            $fileKey = "tabs.$index.settings.features.$featureIndex.image.0.file";
                            if ($this->hasFile($fileKey) || filled($feature['image_url'] ?? null)) {
                                $hasImage = true;
                                break;
                            }
                        }
                    }

                    if (! $hasImage) {
                        $validator->errors()->add(
                            "tabs.$index.settings.features",
                            'حداقل یک تصویر برای گرید سه‌تایی بارگذاری کنید.',
                        );
                    }

                    continue;
                }

                $allowedSources = array_keys(AdminHomeEnumOptions::sourceTypesForContentType($contentType));
                if (! in_array($sourceType, $allowedSources, true)) {
                    $validator->errors()->add(
                        "tabs.$index.source_type",
                        'روش نمایش انتخاب‌شده برای این نوع محتوا مجاز نیست.',
                    );
                }

                $limitedListTypes = array_merge(
                    array_keys(AdminHomeEnumOptions::catalogTabContentTypes()),
                    ['instructor'],
                );
                if (in_array($contentType, $limitedListTypes, true)) {
                    $limit = (int) ($tab['limit'] ?? 0);
                    if ($limit < 1) {
                        $validator->errors()->add(
                            "tabs.$index.limit",
                            'حداکثر آیتم باید حداقل ۱ باشد.',
                        );
                    }
                }

                if ($sourceType !== 'manual') {
                    continue;
                }

                if (empty($tab['manual_item_ids'])) {
                    $validator->errors()->add(
                        "tabs.$index.manual_item_ids",
                        'برای روش «انتخاب دستی» حداقل یک آیتم انتخاب کنید.',
                    );
                }
            }
        });
    }
}
