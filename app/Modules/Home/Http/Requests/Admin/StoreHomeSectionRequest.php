<?php

namespace App\Modules\Home\Http\Requests\Admin;

use App\Modules\Home\Http\Requests\Admin\Concerns\HomeSectionTabValidation;
use App\Modules\Home\Support\AdminHomeEnumOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHomeSectionRequest extends FormRequest
{
    use HomeSectionTabValidation;

    public function authorize(): bool
    {
        return $this->user()?->can('home-sections-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'settings' => ['nullable', 'array'],
            'settings.section_template' => ['nullable', Rule::in(array_keys(AdminHomeEnumOptions::sectionTemplates()))],
            'settings.layout' => ['nullable', 'string', 'max:50'],
            'settings.banner_layout' => ['nullable', 'string', 'max:50'],
            'settings.features' => ['nullable', 'array'],
            'settings.features.*.label' => ['nullable', 'string', 'max:255'],
            'settings.features.*.text' => ['nullable', 'string', 'max:500'],
            'settings.features.*.image_url' => ['nullable', 'string', 'max:500'],
            'settings.show_live_badge' => ['nullable', 'boolean'],
            'settings.promo' => ['nullable', 'array'],
            'settings.promo.title' => ['nullable', 'string', 'max:255'],
            'settings.promo.text' => ['nullable', 'string', 'max:500'],
            'settings.promo.link' => ['nullable', 'string', 'max:500'],
            'settings.promo.image_url' => ['nullable', 'string', 'max:500'],
            'settings.hero' => ['nullable', 'array'],
            'settings.hero.badge' => ['nullable', 'string', 'max:255'],
            'settings.hero.title' => ['nullable', 'string', 'max:255'],
            'settings.hero.subtitle' => ['nullable', 'string', 'max:1000'],
            'settings.hero.cta_primary' => ['nullable', 'string', 'max:255'],
            'settings.hero.cta_primary_url' => ['nullable', 'string', 'max:500'],
            'settings.hero.cta_secondary' => ['nullable', 'string', 'max:255'],
            'settings.hero.cta_secondary_url' => ['nullable', 'string', 'max:500'],
            'settings.hero.highlights' => ['nullable', 'array'],
            'settings.hero.highlights.*.label' => ['nullable', 'string', 'max:255'],
            'settings.hero.highlights.*.text' => ['nullable', 'string', 'max:500'],
            'settings.hero.show_live_badge' => ['nullable', 'boolean'],
            'settings.hero.promo' => ['nullable', 'array'],
            'settings.hero.promo.title' => ['nullable', 'string', 'max:255'],
            'settings.hero.promo.text' => ['nullable', 'string', 'max:500'],
            'settings.hero.promo.link' => ['nullable', 'string', 'max:500'],
            'settings.hero.promo.image_url' => ['nullable', 'string', 'max:500'],
        ], $this->homeSectionTabRules());
    }

    public function withValidator($validator): void
    {
        $this->validateHomeSectionTabs($validator);

        $validator->after(function ($validator) {
            $template = $this->input('settings.section_template', 'catalog');
            if ($template === 'hero_split' && ! filled($this->input('settings.hero.title'))) {
                $validator->errors()->add('settings.hero.title', 'عنوان هیرو را وارد کنید.');
            }

            if ($template === 'features') {
                foreach ($this->input('tabs', []) as $index => $tab) {
                    if (! ($tab['is_active'] ?? true)) {
                        continue;
                    }
                    if (($tab['content_type'] ?? '') !== 'features') {
                        $validator->errors()->add(
                            "tabs.$index.content_type",
                            'در گرید سه‌تایی هر تب باید نوع «گرید سه‌تایی» باشد.',
                        );
                    }
                }
            }

            if ($template === 'catalog') {
                $catalogTypes = array_keys(AdminHomeEnumOptions::catalogTabContentTypes());

                foreach ($this->input('tabs', []) as $index => $tab) {
                    if (! ($tab['is_active'] ?? true)) {
                        continue;
                    }

                    $contentType = (string) ($tab['content_type'] ?? '');
                    if (! in_array($contentType, $catalogTypes, true)) {
                        $validator->errors()->add(
                            "tabs.$index.content_type",
                            'در لیست کارت فقط محصول، دوره، وبلاگ، برند یا دسته‌بندی مجاز است (بنر و گرید سه‌تایی نیست).',
                        );
                    }

                }
            }
        });
    }
}
