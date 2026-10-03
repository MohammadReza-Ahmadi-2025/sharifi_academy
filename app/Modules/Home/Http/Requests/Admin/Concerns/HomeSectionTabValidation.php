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
            'tabs.*.limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'tabs.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'tabs.*.is_active' => ['nullable', 'boolean'],
            'tabs.*.settings' => ['nullable', 'array'],
            'tabs.*.settings.title_url' => ['nullable', 'string', 'max:500'],
            'tabs.*.settings.view_all_url' => ['nullable', 'string', 'max:500'],
            'tabs.*.settings.category_id' => ['nullable', 'integer'],
            'tabs.*.settings.brand_id' => ['nullable', 'integer'],
            'tabs.*.manual_item_ids' => ['nullable', 'array'],
            'tabs.*.manual_item_ids.*' => ['integer', 'min:1'],
            'tabs.*.banner_ids' => ['nullable', 'array'],
            'tabs.*.banner_ids.*' => ['integer', 'exists:banners,id'],
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
                            'برای بنر، روش نمایش باید «انتخاب دستی» باشد.',
                        );
                    }

                    if (empty($tab['banner_ids'])) {
                        $validator->errors()->add(
                            "tabs.$index.banner_ids",
                            'حداقل یک بنر برای این تب انتخاب کنید.',
                        );
                    }

                    continue;
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
