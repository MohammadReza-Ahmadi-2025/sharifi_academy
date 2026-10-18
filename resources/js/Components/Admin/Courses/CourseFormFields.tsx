import { Card } from '@/Components/Admin/shared/ui/card';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, findSelectOptions, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { MultiSelect } from '@/Components/Admin/shared/ui/multi-select';
import { Select } from '@/Components/Admin/shared/ui/select';
import { toGregorianDateOnly } from '@/Components/Admin/shared/lib/datetime-local-value';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import { CatalogSeoFields } from '@/Components/Admin/Catalog/CatalogSeoFields';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

type Option = { value: string | number; label: string };

type CourseFormData = {
    title: string;
    slug: string;
    code: string;
    short_description: string;
    description: string;
    level: string;
    duration_minutes: number | '';
    status: string;
    is_featured: boolean;
    published_at: string;
    category_ids: number[];
    meta_title: string;
    meta_description: string;
    meta_keywords: string;
    canonical: string;
};

type Props = {
    data: CourseFormData;
    errors: Record<string, string>;
    statusOptions: Option[];
    levelOptions: Option[];
    categoryOptions: Option[];
    processing?: boolean;
    showFeatured?: boolean;
    onChange: (key: keyof CourseFormData, value: unknown) => void;
};

export function CourseFormFields({
    data,
    errors,
    statusOptions,
    levelOptions,
    categoryOptions,
    processing = false,
    showFeatured = true,
    onChange,
}: Props) {
    const statusSelectOptions = mapToSelectOptions(statusOptions);
    const levelSelectOptions = mapToSelectOptions(levelOptions);
    const categorySelectOptions = mapToSelectOptions(categoryOptions);

    return (
        <div className="space-y-6">
            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">اطلاعات پایه</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="عنوان" htmlFor="course-title" required error={errors.title}>
                        <AdminInput
                            id="course-title"
                            value={data.title}
                            disabled={processing}
                            onChange={(e) => onChange('title', e.target.value)}
                        />
                    </FormField>
                    <FormField label="اسلاگ" htmlFor="course-slug" error={errors.slug} hint="خالی = از عنوان">
                        <AdminInput
                            id="course-slug"
                            dir="ltr"
                            className="text-left font-mono"
                            value={data.slug}
                            disabled={processing}
                            onChange={(e) => onChange('slug', e.target.value)}
                        />
                    </FormField>
                    <FormField label="کد" htmlFor="course-code" error={errors.code}>
                        <AdminInput
                            id="course-code"
                            value={data.code}
                            disabled={processing}
                            onChange={(e) => onChange('code', e.target.value)}
                        />
                    </FormField>
                    <FormField label="وضعیت" required error={errors.status}>
                        <Select
                            isDisabled={processing}
                            options={statusSelectOptions}
                            value={findSelectOption(statusSelectOptions, data.status)}
                            onChange={(option) => onChange('status', option?.value ?? '')}
                        />
                    </FormField>
                    <FormField label="سطح" error={errors.level}>
                        <Select
                            isDisabled={processing}
                            isClearable
                            placeholder="انتخاب سطح..."
                            options={levelSelectOptions}
                            value={findSelectOption(levelSelectOptions, data.level)}
                            onChange={(option) => onChange('level', option?.value ?? '')}
                        />
                    </FormField>
                    <FormField label="مدت (دقیقه)" htmlFor="course-duration" error={errors.duration_minutes}>
                        <AdminInput
                            id="course-duration"
                            type="number"
                            min={0}
                            value={data.duration_minutes}
                            disabled={processing}
                            onChange={(e) => onChange('duration_minutes', e.target.value ? Number(e.target.value) : '')}
                        />
                    </FormField>
                </div>
            </Card>

            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">محتوا</h2>
                <FormField label="خلاصه" htmlFor="course-short-desc" error={errors.short_description}>
                    <Textarea
                        id="course-short-desc"
                        plain
                        rows={2}
                        value={data.short_description}
                        disabled={processing}
                        onChange={(e) => onChange('short_description', e.target.value)}
                    />
                </FormField>
                <FormField label="توضیحات" htmlFor="course-desc" error={errors.description}>
                    <Textarea
                        id="course-desc"
                        rows={8}
                        value={data.description}
                        disabled={processing}
                        onChange={(e) => onChange('description', e.target.value)}
                    />
                </FormField>
            </Card>

            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">تنظیمات</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="تاریخ انتشار" error={errors.published_at}>
                        <PersianDatePicker
                            value={toGregorianDateOnly(data.published_at)}
                            disabled={processing}
                            placeholder="انتخاب تاریخ انتشار"
                            clearable
                            onChange={(v) => onChange('published_at', v)}
                        />
                    </FormField>
                </div>
                {showFeatured && (
                    <Switch
                        label="نمایش در دوره‌های ویژه"
                        checked={data.is_featured}
                        disabled={processing}
                        onCheckedChange={(v) => onChange('is_featured', v)}
                    />
                )}
                <FormField
                    label="دسته‌های دوره"
                    required
                    error={errors.category_ids}
                    hint={
                        categoryOptions.length === 0
                            ? 'ابتدا در کاتالوگ، دسته با نوع «دوره» تعریف کنید.'
                            : 'حداقل یک دسته انتخاب کنید؛ می‌توانید چند دسته داشته باشید.'
                    }
                >
                    <MultiSelect
                        isDisabled={processing || categoryOptions.length === 0}
                        placeholder="انتخاب دسته‌ها..."
                        options={categorySelectOptions}
                        value={findSelectOptions(categorySelectOptions, data.category_ids)}
                        onChange={(selected) =>
                            onChange('category_ids', selected.map((option) => Number(option.value)))
                        }
                    />
                </FormField>
            </Card>

            <CatalogSeoFields
                data={data}
                errors={errors}
                processing={processing}
                onChange={(key, value) => onChange(key as keyof CourseFormData, value)}
            />
        </div>
    );
}
