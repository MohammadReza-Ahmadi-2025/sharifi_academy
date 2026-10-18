import { HomeSectionAdminPreview } from '@/Components/Admin/Home/HomeSectionAdminPreview';
import { HomeSectionTemplatePicker } from '@/Components/Admin/Home/HomeSectionTemplatePicker';
import {
    allowedSourceTypeKeys,
    contentTypesForTemplate,
    defaultSourceTypeForContentType,
    sanitizeCatalogTabContentType,
    sourceTypesForContentType,
} from '@/Components/Admin/Home/home-section-enums';
import {
    featureGridRowsFromSettings,
    SectionTabFeaturesGridField,
    type FeatureGridRow,
} from '@/Components/Admin/Home/SectionTabFeaturesGridField';
import { SectionTabTestimonialsField } from '@/Components/Admin/Home/SectionTabTestimonialsField';
import {
    CATALOG_SWIPER_SLIDES_PER_VIEW_LG,
    defaultFeatureGridRows,
    defaultFeaturesTab,
    defaultTestimonialsTab,
    defaultHeroSettings,
    formStateForTemplate,
    sectionTemplateId,
    type HomeHeroSettingsForm,
    type HomeSectionTemplateId,
} from '@/Components/Admin/Home/home-section-templates';
import {
    bannerRowsFromPresenter,
    SectionTabBannersField,
} from '@/Components/Admin/Home/SectionTabBannersField';
import { ManualItemPicker, type ManualItem } from '@/Components/Admin/Home/ManualItemPicker';
import type { FileUploadItem } from '@/Components/shared/file-upload';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { Select, type SelectOption } from '@/Components/Admin/shared/ui/select';
import { cn } from '@/lib/utils';
import { reorderByIndex } from '@/Components/shared/reorder-list';
import type { FormDataErrors } from '@inertiajs/core';
import { GripVertical } from 'lucide-react';
import { useState } from 'react';

export type HomeSectionBannerFormRow = {
    id?: number;
    title: string;
    subtitle: string;
    button_text: string;
    button_link: string;
    starts_at: string;
    ends_at: string;
    image: FileUploadItem[];
};

export type HomeSectionTabForm = {
    id?: number;
    title: string;
    content_type: string;
    source_type: string;
    limit: number;
    sort_order: number;
    is_active: boolean;
    settings: {
        title_url?: string;
        view_all_url?: string;
        category_id?: number | null;
        brand_id?: number | null;
        features?: FeatureGridRow[];
    };
    manual_items: ManualItem[];
    manual_item_ids: number[];
    banners?: HomeSectionBannerFormRow[];
};

export type HomeSectionFormData = {
    title: string;
    status: boolean;
    settings: {
        section_template?: HomeSectionTemplateId;
        layout?: string;
        banner_layout?: string;
        catalog_slides_per_view?: number;
        show_live_badge?: boolean;
        promo?: { title: string; text: string; link: string; image_url?: string | null };
        hero?: HomeHeroSettingsForm;
    };
    tabs: HomeSectionTabForm[];
};

type Lookups = {
    product_categories: { id: number; title: string }[];
    course_categories: { id: number; title: string }[];
    brands: { id: number; title: string }[];
};

type Options = {
    content_types: Record<string, string>;
    source_types: Record<string, string>;
    section_layouts: Record<string, string>;
    banner_layouts: Record<string, string>;
};

type Props = {
    data: HomeSectionFormData;
    errors: FormDataErrors<HomeSectionFormData>;
    options: Options;
    lookups: Lookups;
    processing?: boolean;
    onChange: (patch: Partial<HomeSectionFormData>) => void;
    onTabsChange: (tabs: HomeSectionTabForm[]) => void;
};

function recordToOptions(record: Record<string, string>): SelectOption[] {
    return mapToSelectOptions(Object.entries(record).map(([value, label]) => ({ value, label })));
}

function defaultTab(): HomeSectionTabForm {
    return {
        title: 'default',
        content_type: 'course',
        source_type: 'latest',
        limit: 8,
        sort_order: 0,
        is_active: true,
        settings: {},
        manual_items: [],
        manual_item_ids: [],
        banners: [],
    };
}

export function HomeSectionFormFields({ data, errors, options, lookups, processing, onChange, onTabsChange }: Props) {
    const [tabDragIndex, setTabDragIndex] = useState<number | null>(null);
    const [tabDropIndex, setTabDropIndex] = useState<number | null>(null);
    const template = sectionTemplateId(data.settings as Record<string, unknown>, data.tabs);
    const canManageTabs = template === 'catalog' || template === 'features' || template === 'testimonials';
    const showContentTypeSelect = template === 'catalog';
    const hero = { ...defaultHeroSettings(), ...(data.settings.hero ?? {}) };
    const settingsErrors = errors as Record<string, string>;

    const applyTemplate = (id: HomeSectionTemplateId) => {
        const next = formStateForTemplate(id, {
            settings: data.settings as Record<string, unknown>,
            tabs: data.tabs,
        });
        onChange({ settings: next.settings as HomeSectionFormData['settings'] });
        onTabsChange(next.tabs as HomeSectionTabForm[]);
    };

    const updateHero = (patch: Partial<HomeHeroSettingsForm>) => {
        onChange({
            settings: {
                ...data.settings,
                section_template: 'hero_split',
                hero: { ...hero, ...patch },
            },
        });
    };

    const contentTypeOptions = recordToOptions(contentTypesForTemplate(template, options.content_types));
    const bannerLayoutOptions = recordToOptions(options.banner_layouts);

    const updateTab = (index: number, patch: Partial<HomeSectionTabForm>) => {
        const tabs = [...data.tabs];
        const prev = tabs[index];
        const next = { ...prev, ...patch };

        if (patch.content_type !== undefined && patch.content_type !== prev.content_type) {
            const allowed = sourceTypesForContentType(next.content_type, options.source_types);
            const preferred = defaultSourceTypeForContentType(next.content_type);
            next.source_type = allowed[prev.source_type] ? prev.source_type : preferred;
            next.manual_items = [];
            next.manual_item_ids = [];
            if (next.content_type === 'banner') {
                next.banners = [];
            } else {
                next.banners = [];
            }
        }

        if (patch.content_type === 'banner' || next.content_type === 'banner') {
            next.source_type = 'manual';
        }
        if (
            patch.content_type === 'features' ||
            next.content_type === 'features' ||
            patch.content_type === 'testimonials' ||
            next.content_type === 'testimonials'
        ) {
            next.source_type = 'section';
        }

        tabs[index] = next;
        onTabsChange(tabs);
    };

    const setManualItems = (index: number, items: ManualItem[]) => {
        updateTab(index, { manual_items: items, manual_item_ids: items.map((i) => i.id) });
    };

    const firstContentType = data.tabs[0]?.content_type;
    const tabErrors = errors as Record<string, string>;

    return (
        <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(240px,300px)] lg:items-start">
            <div className="min-w-0 space-y-6">
            <Card className="w-full space-y-4 p-6">
                <div>
                    <p className="mb-3 text-sm font-medium text-slate-700 dark:text-slate-200">نوع و شکل سکشن</p>
                    <HomeSectionTemplatePicker
                        value={template}
                        disabled={processing}
                        onChange={applyTemplate}
                    />
                </div>
            </Card>

            <Card className="w-full space-y-4 p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="عنوان سکشن" required error={errors.title} className="md:col-span-2">
                        <AdminInput
                            value={data.title}
                            onChange={(e) => onChange({ title: e.target.value })}
                            disabled={processing}
                        />
                    </FormField>
                    <FormField label="فعال در صفحه اصلی" className="md:col-span-2">
                        <Switch
                            label="نمایش برای بازدیدکنندگان"
                            checked={data.status}
                            disabled={processing}
                            onCheckedChange={(v) => onChange({ status: v })}
                        />
                    </FormField>
                    {template === 'catalog' && (
                        <>
                            <FormField label="تعداد کارت در دسکتاپ (اسلایدر)" className="md:col-span-2">
                                <Select
                                    options={[2, 3, 4, 5, 6].map((n) => ({
                                        value: String(n),
                                        label: `${n} کارت هم‌زمان`,
                                    }))}
                                    value={findSelectOption(
                                        [2, 3, 4, 5, 6].map((n) => ({
                                            value: String(n),
                                            label: `${n} کارت هم‌زمان`,
                                        })),
                                        String(data.settings.catalog_slides_per_view ?? CATALOG_SWIPER_SLIDES_PER_VIEW_LG),
                                    )}
                                    onChange={(opt) =>
                                        onChange({
                                            settings: {
                                                ...data.settings,
                                                catalog_slides_per_view: opt
                                                    ? Number.parseInt(opt.value, 10)
                                                    : CATALOG_SWIPER_SLIDES_PER_VIEW_LG,
                                            },
                                        })
                                    }
                                    isDisabled={processing}
                                    isSearchable={false}
                                />
                            </FormField>
                            <p className="md:col-span-2 text-sm text-slate-600 dark:text-slate-400">
                                نمایش به‌صورت اسلایدر (Swiper)؛ تعداد آیتم هر تب را با «حداکثر آیتم» یا انتخاب دستی تنظیم کنید (تا ۵۰).
                            </p>
                        </>
                    )}
                    {template === 'banners' && (
                        <FormField label="چیدمان بنر" className="md:col-span-2">
                            <Select
                                options={bannerLayoutOptions}
                                value={findSelectOption(bannerLayoutOptions, data.settings.banner_layout ?? 'showcase')}
                                onChange={(opt) =>
                                    onChange({ settings: { ...data.settings, banner_layout: opt?.value ?? 'showcase' } })
                                }
                                isDisabled={processing}
                                isSearchable={false}
                            />
                        </FormField>
                    )}
                    {template === 'features' && (
                        <p className="md:col-span-2 text-sm text-slate-600 dark:text-slate-400">
                            هر تب یک گرید سه‌تایی جداگانه است؛ از بخش «تب‌های سکشن» تب اضافه کنید.
                        </p>
                    )}
                    {template === 'testimonials' && (
                        <p className="md:col-span-2 text-sm text-slate-600 dark:text-slate-400">
                            نام، نقش و متن هر نظر را در تب‌ها تعریف کنید (تا ۱۲ نظر در هر تب).
                        </p>
                    )}
                    {template === 'instructors' && (
                        <>
                            <FormField label="تعداد کارت در دسکتاپ (اسلایدر)" className="md:col-span-2">
                                <Select
                                    options={[2, 3, 4, 5, 6].map((n) => ({
                                        value: String(n),
                                        label: `${n} کارت هم‌زمان`,
                                    }))}
                                    value={findSelectOption(
                                        [2, 3, 4, 5, 6].map((n) => ({
                                            value: String(n),
                                            label: `${n} کارت هم‌زمان`,
                                        })),
                                        String(data.settings.catalog_slides_per_view ?? CATALOG_SWIPER_SLIDES_PER_VIEW_LG),
                                    )}
                                    onChange={(opt) =>
                                        onChange({
                                            settings: {
                                                ...data.settings,
                                                catalog_slides_per_view: opt
                                                    ? Number.parseInt(opt.value, 10)
                                                    : CATALOG_SWIPER_SLIDES_PER_VIEW_LG,
                                            },
                                        })
                                    }
                                    isDisabled={processing}
                                    isSearchable={false}
                                />
                            </FormField>
                            <p className="md:col-span-2 text-sm text-slate-600 dark:text-slate-400">
                                کاربران با نقش «استاد» و وضعیت فعال. در حالت خودکار جدیدترین‌ها نمایش داده می‌شوند؛ با
                                کلیک بازدیدکننده، رزومه عمومی استاد باز می‌شود.
                            </p>
                        </>
                    )}
                </div>
            </Card>

            {template === 'hero_split' && (
                <Card className="w-full space-y-4 p-6">
                    <h3 className="text-lg font-semibold text-slate-800 dark:text-slate-100">متن و دکمه‌های هیرو (سمت چپ)</h3>
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="برچسب بالا" className="md:col-span-2">
                            <AdminInput
                                value={hero.badge}
                                disabled={processing}
                                onChange={(e) => updateHero({ badge: e.target.value })}
                            />
                        </FormField>
                        <FormField label="عنوان اصلی" required error={settingsErrors['settings.hero.title']} className="md:col-span-2">
                            <AdminInput
                                value={hero.title}
                                disabled={processing}
                                onChange={(e) => updateHero({ title: e.target.value })}
                            />
                        </FormField>
                        <FormField label="زیرعنوان" className="md:col-span-2">
                            <AdminInput
                                value={hero.subtitle}
                                disabled={processing}
                                onChange={(e) => updateHero({ subtitle: e.target.value })}
                            />
                        </FormField>
                        <FormField label="دکمه اصلی — متن">
                            <AdminInput
                                value={hero.cta_primary}
                                disabled={processing}
                                onChange={(e) => updateHero({ cta_primary: e.target.value })}
                            />
                        </FormField>
                        <FormField label="دکمه اصلی — لینک">
                            <AdminInput
                                dir="ltr"
                                value={hero.cta_primary_url}
                                disabled={processing}
                                onChange={(e) => updateHero({ cta_primary_url: e.target.value })}
                            />
                        </FormField>
                        <FormField label="دکمه ثانویه — متن">
                            <AdminInput
                                value={hero.cta_secondary}
                                disabled={processing}
                                onChange={(e) => updateHero({ cta_secondary: e.target.value })}
                            />
                        </FormField>
                        <FormField label="دکمه ثانویه — لینک">
                            <AdminInput
                                dir="ltr"
                                value={hero.cta_secondary_url}
                                disabled={processing}
                                onChange={(e) => updateHero({ cta_secondary_url: e.target.value })}
                            />
                        </FormField>
                        <div className="md:col-span-2 space-y-3">
                            <p className="text-sm font-medium text-slate-700 dark:text-slate-200">نکات برجسته</p>
                            {(hero.highlights ?? []).map((item, featureIndex) => (
                                <div key={featureIndex} className="grid gap-2 md:grid-cols-2">
                                    <AdminInput
                                        placeholder="عنوان"
                                        value={item.label}
                                        disabled={processing}
                                        onChange={(e) => {
                                            const highlights = [...(hero.highlights ?? [])];
                                            highlights[featureIndex] = { ...highlights[featureIndex], label: e.target.value };
                                            updateHero({ highlights });
                                        }}
                                    />
                                    <AdminInput
                                        placeholder="توضیح کوتاه (اختیاری)"
                                        value={item.text}
                                        disabled={processing}
                                        onChange={(e) => {
                                            const highlights = [...(hero.highlights ?? [])];
                                            highlights[featureIndex] = { ...highlights[featureIndex], text: e.target.value };
                                            updateHero({ highlights });
                                        }}
                                    />
                                </div>
                            ))}
                        </div>
                        <FormField label="نشان Live روی اسلاید" className="md:col-span-2">
                            <Switch
                                label="نمایش"
                                checked={hero.show_live_badge}
                                disabled={processing}
                                onCheckedChange={(v) => updateHero({ show_live_badge: v })}
                            />
                        </FormField>
                        <p className="md:col-span-2 text-sm font-medium text-slate-700 dark:text-slate-200">کارت تبلیغ (روی اسلاید)</p>
                        <FormField label="عنوان کارت">
                            <AdminInput
                                value={hero.promo.title}
                                disabled={processing}
                                onChange={(e) => updateHero({ promo: { ...hero.promo, title: e.target.value } })}
                            />
                        </FormField>
                        <FormField label="لینک کارت">
                            <AdminInput
                                dir="ltr"
                                value={hero.promo.link}
                                disabled={processing}
                                onChange={(e) => updateHero({ promo: { ...hero.promo, link: e.target.value } })}
                            />
                        </FormField>
                        <FormField label="متن کارت" className="md:col-span-2">
                            <AdminInput
                                value={hero.promo.text}
                                disabled={processing}
                                onChange={(e) => updateHero({ promo: { ...hero.promo, text: e.target.value } })}
                            />
                        </FormField>
                    </div>
                </Card>
            )}

            <div className="flex w-full items-center justify-between">
                <h3 className="text-lg font-semibold text-slate-800 dark:text-slate-100">
                    {template === 'hero_split' ? 'اسلاید بنر (سمت راست)' : 'تب‌های سکشن'}
                </h3>
                {canManageTabs && (
                    <AdminButton
                        type="button"
                        variant="outline"
                        disabled={processing}
                        onClick={() =>
                            onTabsChange([
                                ...data.tabs,
                                template === 'features'
                                    ? { ...defaultFeaturesTab(), sort_order: data.tabs.length }
                                    : template === 'testimonials'
                                      ? { ...defaultTestimonialsTab(), sort_order: data.tabs.length }
                                      : {
                                          ...defaultTab(),
                                          sort_order: data.tabs.length,
                                          limit: 12,
                                          banners: [],
                                      },
                            ])
                        }
                    >
                        افزودن تب
                    </AdminButton>
                )}
            </div>

            {canManageTabs && data.tabs.length > 1 && (
                <p className="text-sm text-slate-500">برای تغییر ترتیب تب‌ها، کارت‌ها را با دستگیره بکشید.</p>
            )}

            {data.tabs.map((tab, index) => {
                const canDragTabs = canManageTabs && data.tabs.length > 1 && !processing;
                const sourceOptions = recordToOptions(
                    sourceTypesForContentType(tab.content_type, options.source_types),
                );
                const showBannerPicker =
                    tab.content_type === 'banner' && (template === 'hero_split' || template === 'banners');
                const productCategoryOptions = mapToSelectOptions(
                    lookups.product_categories.map((c) => ({ value: c.id, label: c.title })),
                );
                const courseCategoryOptions = mapToSelectOptions(
                    lookups.course_categories.map((c) => ({ value: c.id, label: c.title })),
                );
                const brandOptions = mapToSelectOptions(lookups.brands.map((b) => ({ value: b.id, label: b.title })));

                return (
                    <Card
                        key={tab.id ?? `new-${index}`}
                        className={cn(
                            'w-full space-y-4 p-6',
                            tabDragIndex === index && 'opacity-50',
                            tabDropIndex === index && tabDragIndex !== index && 'bg-slate-50 dark:bg-slate-900/40',
                        )}
                        draggable={canDragTabs}
                        onDragStart={() => canDragTabs && setTabDragIndex(index)}
                        onDragEnd={() => {
                            setTabDragIndex(null);
                            setTabDropIndex(null);
                        }}
                        onDragOver={(e) => {
                            if (!canDragTabs || tabDragIndex === null) {
                                return;
                            }
                            e.preventDefault();
                            setTabDropIndex(index);
                        }}
                        onDrop={() => {
                            if (tabDragIndex === null || tabDragIndex === index) {
                                setTabDragIndex(null);
                                setTabDropIndex(null);

                                return;
                            }
                            onTabsChange(reorderByIndex(data.tabs, tabDragIndex, index));
                            setTabDragIndex(null);
                            setTabDropIndex(null);
                        }}
                    >
                        <div className="flex items-center justify-between gap-2">
                            <div className="flex items-center gap-2">
                                {canDragTabs ? (
                                    <span className="cursor-grab text-slate-400 active:cursor-grabbing" aria-hidden>
                                        <GripVertical className="size-5" />
                                    </span>
                                ) : null}
                                <h4 className="font-semibold">تب {index + 1}</h4>
                            </div>
                            {canManageTabs && data.tabs.length > 1 && (
                                <AdminButton
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    disabled={processing}
                                    onClick={() => onTabsChange(data.tabs.filter((_, i) => i !== index))}
                                >
                                    حذف تب
                                </AdminButton>
                            )}
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <FormField label="عنوان تب">
                                <AdminInput
                                    value={tab.title}
                                    onChange={(e) => updateTab(index, { title: e.target.value })}
                                    disabled={processing}
                                />
                            </FormField>
                            <FormField label="تب فعال">
                                <Switch
                                    label="فعال"
                                    checked={tab.is_active}
                                    disabled={processing}
                                    onCheckedChange={(v) => updateTab(index, { is_active: v })}
                                />
                            </FormField>
                            {showContentTypeSelect && contentTypeOptions.length > 0 && (
                                <FormField label="نوع محتوا">
                                    <Select
                                        options={contentTypeOptions}
                                        value={findSelectOption(contentTypeOptions, tab.content_type)}
                                        onChange={(opt) => {
                                            updateTab(index, {
                                                content_type: opt?.value ?? 'course',
                                            });
                                        }}
                                        isDisabled={processing}
                                        isSearchable={false}
                                        menuPlacement="top"
                                    />
                                </FormField>
                            )}
                            {!['banner', 'features', 'testimonials'].includes(tab.content_type) && (
                                <FormField label="روش نمایش">
                                    <Select
                                        options={sourceOptions}
                                        value={findSelectOption(sourceOptions, tab.source_type)}
                                        onChange={(opt) =>
                                            updateTab(index, {
                                                source_type: opt?.value ?? defaultSourceTypeForContentType(tab.content_type),
                                            })
                                        }
                                        isDisabled={processing}
                                        isSearchable={false}
                                        menuPlacement="top"
                                    />
                                </FormField>
                            )}
                            {!['banner', 'features', 'testimonials'].includes(tab.content_type) && (
                                <FormField
                                    label="حداکثر آیتم"
                                    hint={
                                        template === 'catalog' || template === 'instructors'
                                            ? `در انتخاب دستی همه آیتم‌های انتخاب‌شده نمایش داده می‌شوند؛ در حالت خودکار تا این عدد`
                                            : undefined
                                    }
                                >
                                    <AdminInput
                                        type="number"
                                        min={1}
                                        max={50}
                                        value={tab.limit}
                                        onChange={(e) => updateTab(index, { limit: Number(e.target.value) })}
                                        disabled={processing || tab.source_type === 'manual'}
                                    />
                                </FormField>
                            )}
                            {template === 'catalog' && (
                                <FormField label="لینک «مشاهده همه»" className="md:col-span-2">
                                    <AdminInput
                                        dir="ltr"
                                        value={tab.settings.view_all_url ?? ''}
                                        onChange={(e) =>
                                            updateTab(index, {
                                                settings: { ...tab.settings, view_all_url: e.target.value },
                                            })
                                        }
                                        disabled={processing}
                                    />
                                </FormField>
                            )}
                            {template === 'features' && tab.content_type === 'features' && (
                                <SectionTabFeaturesGridField
                                    tab={tab}
                                    disabled={processing}
                                    error={tabErrors[`tabs.${index}.settings.features`]}
                                    onChange={(settings) => updateTab(index, { settings })}
                                />
                            )}
                            {template === 'testimonials' && tab.content_type === 'testimonials' && (
                                <SectionTabTestimonialsField
                                    tab={tab}
                                    disabled={processing}
                                    error={tabErrors[`tabs.${index}.settings.testimonials`]}
                                    onChange={(settings) => updateTab(index, { settings })}
                                />
                            )}
                            {tab.source_type === 'by_category' && tab.content_type === 'product' && (
                                <FormField label="دسته محصول" className="md:col-span-2">
                                    <Select
                                        options={productCategoryOptions}
                                        value={findSelectOption(productCategoryOptions, tab.settings.category_id)}
                                        onChange={(opt) =>
                                            updateTab(index, {
                                                settings: {
                                                    ...tab.settings,
                                                    category_id: opt ? Number(opt.value) : null,
                                                },
                                            })
                                        }
                                        isDisabled={processing}
                                    />
                                </FormField>
                            )}
                            {tab.source_type === 'by_category' && tab.content_type === 'course' && (
                                <FormField label="دسته دوره" className="md:col-span-2">
                                    <Select
                                        options={courseCategoryOptions}
                                        value={findSelectOption(courseCategoryOptions, tab.settings.category_id)}
                                        onChange={(opt) =>
                                            updateTab(index, {
                                                settings: {
                                                    ...tab.settings,
                                                    category_id: opt ? Number(opt.value) : null,
                                                },
                                            })
                                        }
                                        isDisabled={processing}
                                    />
                                </FormField>
                            )}
                            {tab.source_type === 'by_brand' && tab.content_type === 'product' && (
                                <FormField label="برند" className="md:col-span-2">
                                    <Select
                                        options={brandOptions}
                                        value={findSelectOption(brandOptions, tab.settings.brand_id)}
                                        onChange={(opt) =>
                                            updateTab(index, {
                                                settings: {
                                                    ...tab.settings,
                                                    brand_id: opt ? Number(opt.value) : null,
                                                },
                                            })
                                        }
                                        isDisabled={processing}
                                    />
                                </FormField>
                            )}
                            {showBannerPicker && (
                                <SectionTabBannersField
                                    tabIndex={index}
                                    banners={tab.banners ?? []}
                                    disabled={processing}
                                    error={tabErrors[`tabs.${index}.banners`] as string | undefined}
                                    onChange={(banners) => updateTab(index, { banners })}
                                />
                            )}
                            {tab.source_type === 'manual' && tab.content_type !== 'banner' && (
                                <ManualItemPicker
                                    contentType={tab.content_type}
                                    items={tab.manual_items}
                                    onChange={(items) => setManualItems(index, items)}
                                    disabled={processing}
                                    error={tabErrors[`tabs.${index}.manual_item_ids`]}
                                />
                            )}
                        </div>
                    </Card>
                );
            })}
            </div>

            <div className="lg:sticky lg:top-4">
                <HomeSectionAdminPreview data={data} />
            </div>
        </div>
    );
}

export function emptyHomeSectionForm(): HomeSectionFormData {
    const seeded = formStateForTemplate('hero_split', {
        settings: {},
        tabs: [defaultTab()],
    });

    return {
        title: '',
        status: true,
        settings: seeded.settings as HomeSectionFormData['settings'],
        tabs: seeded.tabs as HomeSectionTabForm[],
    };
}

export function sectionToFormData(section: {
    title: string;
    status: boolean;
    settings?: HomeSectionFormData['settings'];
    tabs: (Omit<HomeSectionTabForm, 'manual_items' | 'banners'> & {
        manual_items?: ManualItem[];
        banners?: Array<Record<string, unknown>>;
    })[];
}): HomeSectionFormData {
    const settings = { ...(section.settings ?? {}) };
    if (!settings.section_template) {
        settings.section_template = sectionTemplateId(settings as Record<string, unknown>, section.tabs);
    }

    const resolvedTemplate = sectionTemplateId(settings as Record<string, unknown>, section.tabs);

    if (resolvedTemplate === 'features' && Array.isArray(settings.features) && settings.features.length > 0) {
        const [first, ...rest] = section.tabs;
        const migratedFirst = first
            ? {
                  ...first,
                  content_type: 'features',
                  source_type: 'section',
                  settings: {
                      ...(first.settings ?? {}),
                      features: settings.features.slice(0, 3),
                  },
              }
            : defaultFeaturesTab();
        section = { ...section, tabs: [migratedFirst, ...rest] };
        delete settings.features;
    }

    return {
        title: section.title,
        status: section.status,
        settings,
        tabs: section.tabs.map((tab) => {
            let content_type = tab.content_type;
            let source_type = tab.source_type;
            if (resolvedTemplate === 'catalog') {
                content_type = sanitizeCatalogTabContentType(content_type);
                if (!allowedSourceTypeKeys(content_type).includes(source_type)) {
                    source_type = defaultSourceTypeForContentType(content_type);
                }
            }

            if (resolvedTemplate === 'features') {
                content_type = 'features';
                source_type = 'section';
            }

            if (resolvedTemplate === 'testimonials') {
                content_type = 'testimonials';
                source_type = 'section';
            }

            if (resolvedTemplate === 'instructors') {
                content_type = 'instructor';
                if (!allowedSourceTypeKeys('instructor').includes(source_type)) {
                    source_type = defaultSourceTypeForContentType('instructor');
                }
            }

            const tabSettings = { ...(tab.settings ?? {}) };
            if (resolvedTemplate === 'features') {
                tabSettings.features = featureGridRowsFromSettings(tabSettings.features as FeatureGridRow[] | undefined);
            }

            return {
                ...tab,
                content_type,
                source_type,
                manual_items: tab.manual_items ?? [],
                manual_item_ids: tab.manual_item_ids ?? tab.manual_items?.map((i) => i.id) ?? [],
                banners:
                    content_type === 'banner'
                        ? bannerRowsFromPresenter(
                              (tab.banners as Parameters<typeof bannerRowsFromPresenter>[0]) ?? [],
                          )
                        : undefined,
                settings: tabSettings,
            };
        }),
    };
}
