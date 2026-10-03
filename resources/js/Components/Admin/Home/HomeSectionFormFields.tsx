import { ManualItemPicker, type ManualItem } from '@/Components/Admin/Home/ManualItemPicker';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { Select, type SelectOption } from '@/Components/Admin/shared/ui/select';
import type { FormDataErrors } from '@inertiajs/core';

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
    };
    manual_items: ManualItem[];
    manual_item_ids: number[];
    banner_ids: number[];
};

export type HomeSectionFormData = {
    title: string;
    status: boolean;
    sort: number;
    settings: { layout?: string; banner_layout?: string };
    tabs: HomeSectionTabForm[];
};

type Lookups = {
    banners: { id: number; title: string; image_url?: string | null }[];
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
        banner_ids: [],
    };
}

function sourceTypesForTab(contentType: string, all: Record<string, string>): Record<string, string> {
    if (contentType === 'banner') {
        return { manual: 'انتخاب دستی (از لیست بنرها)' };
    }
    return all;
}

export function HomeSectionFormFields({ data, errors, options, lookups, processing, onChange, onTabsChange }: Props) {
    const contentTypeOptions = recordToOptions(options.content_types);
    const layoutOptions = recordToOptions(options.section_layouts);
    const bannerLayoutOptions = recordToOptions(options.banner_layouts);

    const updateTab = (index: number, patch: Partial<HomeSectionTabForm>) => {
        const tabs = [...data.tabs];
        const next = { ...tabs[index], ...patch };

        if (patch.content_type === 'banner') {
            next.source_type = 'manual';
        }

        tabs[index] = next;
        onTabsChange(tabs);
    };

    const setManualItems = (index: number, items: ManualItem[]) => {
        updateTab(index, { manual_items: items, manual_item_ids: items.map((i) => i.id) });
    };

    const toggleBanner = (index: number, bannerId: number) => {
        const tab = data.tabs[index];
        const ids = tab.banner_ids.includes(bannerId)
            ? tab.banner_ids.filter((id) => id !== bannerId)
            : [...tab.banner_ids, bannerId];
        updateTab(index, { banner_ids: ids });
    };

    const firstContentType = data.tabs[0]?.content_type;
    const tabErrors = errors as Record<string, string>;

    return (
        <div className="space-y-6">
            <Card className="max-w-4xl space-y-4 p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="عنوان سکشن" required error={errors.title} className="md:col-span-2">
                        <AdminInput
                            value={data.title}
                            onChange={(e) => onChange({ title: e.target.value })}
                            disabled={processing}
                        />
                    </FormField>
                    <FormField label="ترتیب نمایش">
                        <AdminInput
                            type="number"
                            value={data.sort}
                            onChange={(e) => onChange({ sort: Number(e.target.value) })}
                            disabled={processing}
                        />
                    </FormField>
                    <FormField label="فعال در صفحه اصلی">
                        <label className="flex items-center gap-2 pt-2 text-sm">
                            <input
                                type="checkbox"
                                checked={data.status}
                                onChange={(e) => onChange({ status: e.target.checked })}
                                disabled={processing}
                                className="size-4 rounded border-slate-300"
                            />
                            نمایش برای بازدیدکنندگان
                        </label>
                    </FormField>
                    {firstContentType && firstContentType !== 'banner' && (
                        <FormField label="چیدمان" className="md:col-span-2">
                            <Select
                                options={layoutOptions}
                                value={findSelectOption(layoutOptions, data.settings.layout ?? 'grid')}
                                onChange={(opt) => onChange({ settings: { ...data.settings, layout: opt?.value ?? 'grid' } })}
                                isDisabled={processing}
                                isSearchable={false}
                            />
                        </FormField>
                    )}
                    {firstContentType === 'banner' && (
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
                </div>
            </Card>

            <div className="flex max-w-4xl items-center justify-between">
                <h3 className="text-lg font-semibold text-slate-800 dark:text-slate-100">تب‌های سکشن</h3>
                <AdminButton
                    type="button"
                    variant="outline"
                    disabled={processing}
                    onClick={() => onTabsChange([...data.tabs, { ...defaultTab(), sort_order: data.tabs.length }])}
                >
                    افزودن تب
                </AdminButton>
            </div>

            {data.tabs.map((tab, index) => {
                const sourceOptions = recordToOptions(sourceTypesForTab(tab.content_type, options.source_types));
                const productCategoryOptions = mapToSelectOptions(
                    lookups.product_categories.map((c) => ({ value: c.id, label: c.title })),
                );
                const courseCategoryOptions = mapToSelectOptions(
                    lookups.course_categories.map((c) => ({ value: c.id, label: c.title })),
                );
                const brandOptions = mapToSelectOptions(lookups.brands.map((b) => ({ value: b.id, label: b.title })));

                return (
                    <Card key={tab.id ?? `new-${index}`} className="max-w-4xl space-y-4 p-6">
                        <div className="flex items-center justify-between">
                            <h4 className="font-semibold">تب {index + 1}</h4>
                            {data.tabs.length > 1 && (
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
                                <label className="flex items-center gap-2 pt-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={tab.is_active}
                                        onChange={(e) => updateTab(index, { is_active: e.target.checked })}
                                        disabled={processing}
                                        className="size-4 rounded border-slate-300"
                                    />
                                    فعال
                                </label>
                            </FormField>
                            <FormField label="نوع محتوا">
                                <Select
                                    options={contentTypeOptions}
                                    value={findSelectOption(contentTypeOptions, tab.content_type)}
                                    onChange={(opt) =>
                                        updateTab(index, {
                                            content_type: opt?.value ?? 'course',
                                            source_type: opt?.value === 'banner' ? 'manual' : tab.source_type,
                                        })
                                    }
                                    isDisabled={processing}
                                    isSearchable={false}
                                />
                            </FormField>
                            {tab.content_type !== 'banner' && (
                                <FormField label="روش نمایش">
                                    <Select
                                        options={sourceOptions}
                                        value={findSelectOption(sourceOptions, tab.source_type)}
                                        onChange={(opt) => updateTab(index, { source_type: opt?.value ?? 'latest' })}
                                        isDisabled={processing}
                                        isSearchable={false}
                                    />
                                </FormField>
                            )}
                            {tab.content_type !== 'banner' && (
                                <FormField label="حداکثر آیتم">
                                    <AdminInput
                                        type="number"
                                        min={1}
                                        max={50}
                                        value={tab.limit}
                                        onChange={(e) => updateTab(index, { limit: Number(e.target.value) })}
                                        disabled={processing}
                                    />
                                </FormField>
                            )}
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
                            {tab.content_type === 'banner' && (
                                <FormField
                                    label="بنرهای انتخاب‌شده"
                                    hint="از منوی «بنرهای خانه» بنر بسازید و اینجا انتخاب کنید."
                                    error={tabErrors[`tabs.${index}.banner_ids`]}
                                    className="md:col-span-2"
                                >
                                    <div className="grid gap-2 sm:grid-cols-2">
                                        {lookups.banners.map((banner) => {
                                            const selected = tab.banner_ids.includes(banner.id);
                                            return (
                                                <button
                                                    key={banner.id}
                                                    type="button"
                                                    disabled={processing}
                                                    onClick={() => toggleBanner(index, banner.id)}
                                                    className={`flex items-center gap-3 rounded-lg border p-2 text-right text-sm transition ${
                                                        selected
                                                            ? 'border-slate-900 bg-slate-50 dark:border-slate-300 dark:bg-slate-900'
                                                            : 'border-slate-200 dark:border-slate-700'
                                                    }`}
                                                >
                                                    {banner.image_url ? (
                                                        <img src={banner.image_url} alt="" className="size-12 rounded object-cover" />
                                                    ) : (
                                                        <div className="size-12 rounded bg-slate-100 dark:bg-slate-800" />
                                                    )}
                                                    <span className="flex-1">{banner.title}</span>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </FormField>
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
    );
}

export function emptyHomeSectionForm(): HomeSectionFormData {
    return {
        title: '',
        status: true,
        sort: 0,
        settings: { layout: 'grid' },
        tabs: [defaultTab()],
    };
}

export function sectionToFormData(section: {
    title: string;
    status: boolean;
    sort: number;
    settings?: HomeSectionFormData['settings'];
    tabs: (Omit<HomeSectionTabForm, 'manual_items'> & { manual_items?: ManualItem[] })[];
}): HomeSectionFormData {
    return {
        title: section.title,
        status: section.status,
        sort: section.sort,
        settings: section.settings ?? {},
        tabs: section.tabs.map((tab) => ({
            ...tab,
            manual_items: tab.manual_items ?? [],
            manual_item_ids: tab.manual_item_ids ?? tab.manual_items?.map((i) => i.id) ?? [],
            banner_ids: tab.banner_ids ?? [],
            settings: tab.settings ?? {},
        })),
    };
}
