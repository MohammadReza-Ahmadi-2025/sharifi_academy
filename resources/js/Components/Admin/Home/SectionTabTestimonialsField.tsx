import type { HomeSectionTabForm } from '@/Components/Admin/Home/HomeSectionFormFields';
import { TESTIMONIALS_MAX, type TestimonialRow } from '@/Components/Admin/Home/home-section-templates';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import { clampTestimonialRating } from '@/Components/Home/HomeTestimonialStars';
import { Plus, Trash2 } from 'lucide-react';

const RATING_OPTIONS = mapToSelectOptions(
    [5, 4, 3, 2, 1].map((n) => ({ value: String(n), label: `${n} از ۵` })),
);

type Props = {
    tab: HomeSectionTabForm;
    disabled?: boolean;
    error?: string;
    onChange: (settings: HomeSectionTabForm['settings']) => void;
};

function rowsFromSettings(raw: unknown): TestimonialRow[] {
    if (!Array.isArray(raw)) {
        return [];
    }

    return raw
        .map((row) => {
            if (!row || typeof row !== 'object') {
                return null;
            }
            const r = row as TestimonialRow;

            return {
                name: typeof r.name === 'string' ? r.name : '',
                role: typeof r.role === 'string' ? r.role : '',
                quote: typeof r.quote === 'string' ? r.quote : '',
                rating: clampTestimonialRating(r.rating),
            };
        })
        .filter((row): row is TestimonialRow => row !== null);
}

export function SectionTabTestimonialsField({ tab, disabled = false, error, onChange }: Props) {
    const rows = rowsFromSettings(tab.settings?.testimonials);

    const updateRows = (next: TestimonialRow[]) => {
        onChange({ ...tab.settings, testimonials: next });
    };

    const updateRow = (index: number, patch: Partial<TestimonialRow>) => {
        const next = rows.map((row, i) => (i === index ? { ...row, ...patch } : row));
        updateRows(next);
    };

    const addRow = () => {
        if (rows.length >= TESTIMONIALS_MAX) {
            return;
        }
        updateRows([...rows, { name: '', role: '', quote: '', rating: 5 }]);
    };

    const removeRow = (index: number) => {
        updateRows(rows.filter((_, i) => i !== index));
    };

    return (
        <div className="md:col-span-2 space-y-4">
            <div className="flex items-center justify-between gap-3">
                <p className="text-sm font-medium text-slate-700 dark:text-slate-200">نظرات کاربران</p>
                <AdminButton type="button" variant="outline" size="sm" disabled={disabled || rows.length >= TESTIMONIALS_MAX} onClick={addRow}>
                    <Plus className="size-4" />
                    افزودن نظر
                </AdminButton>
            </div>
            {error && <p className="text-sm text-red-600">{error}</p>}
            {rows.length === 0 ? (
                <p className="text-sm text-slate-500">حداقل یک نظر با نام و متن اضافه کنید.</p>
            ) : (
                <div className="space-y-4">
                    {rows.map((row, index) => (
                        <div
                            key={index}
                            className="space-y-3 rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/40"
                        >
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-semibold text-slate-500">نظر {index + 1}</span>
                                <AdminButton
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    disabled={disabled}
                                    onClick={() => removeRow(index)}
                                >
                                    <Trash2 className="size-4" />
                                </AdminButton>
                            </div>
                            <div className="grid gap-3 md:grid-cols-3">
                                <FormField label="نام">
                                    <AdminInput
                                        value={row.name}
                                        disabled={disabled}
                                        onChange={(e) => updateRow(index, { name: e.target.value })}
                                        placeholder="مثلاً: امیر حسینی"
                                    />
                                </FormField>
                                <FormField label="نقش / عنوان">
                                    <AdminInput
                                        value={row.role}
                                        disabled={disabled}
                                        onChange={(e) => updateRow(index, { role: e.target.value })}
                                        placeholder="مهندس مکانیک"
                                    />
                                </FormField>
                                <FormField label="امتیاز">
                                    <Select
                                        isDisabled={disabled}
                                        options={RATING_OPTIONS}
                                        value={findSelectOption(RATING_OPTIONS, String(clampTestimonialRating(row.rating)))}
                                        onChange={(opt) =>
                                            updateRow(index, { rating: clampTestimonialRating(opt?.value ?? 5) })
                                        }
                                        isSearchable={false}
                                    />
                                </FormField>
                            </div>
                            <FormField label="متن نظر">
                                <Textarea
                                    rows={3}
                                    value={row.quote}
                                    disabled={disabled}
                                    onChange={(e) => updateRow(index, { quote: e.target.value })}
                                    placeholder="تجربه شما از دوره‌های آکادمی..."
                                />
                            </FormField>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
