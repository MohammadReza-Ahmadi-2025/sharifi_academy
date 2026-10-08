export type CatalogVariantAttributeValue = {
    id: number;
    value: string;
    slug: string;
    code: string | null;
};

export type CatalogVariantAttribute = {
    id: number;
    name: string;
    slug: string;
    type: string;
    values: CatalogVariantAttributeValue[];
};

export type VariantAttributeSelection = {
    attributeId: number;
    slug: string;
    name: string;
    type: string;
    selectedValueIds: number[];
};

import { cn } from '@/lib/utils';

export const MAX_VARIANT_ATTRIBUTES = 3;

/** دکمه/چیپ مقدار ویژگی — همان الگوی Nexin (sky برای انتخاب‌شده) */
export function variantValueChipClass(active: boolean): string {
    return cn(
        'rounded-lg border px-3 py-1.5 text-sm transition-colors disabled:cursor-not-allowed disabled:opacity-50',
        active
            ? 'border-sky-300 bg-sky-50 font-medium text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300'
            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400 dark:hover:border-slate-700',
    );
}

export function countAttributeCombinations(selections: VariantAttributeSelection[]): number {
    const active = selections.filter((row) => row.selectedValueIds.length > 0);

    if (active.length === 0) {
        return 0;
    }

    return active.reduce((total, row) => total * row.selectedValueIds.length, 1);
}

export function swatchColor(value: CatalogVariantAttributeValue): string | null {
    const code = value.code?.trim() ?? '';

    if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(code)) {
        return code;
    }

    const map: Record<string, string> = {
        مشکی: '#111827',
        سفید: '#F9FAFB',
        آبی: '#2563EB',
        قرمز: '#DC2626',
        سبز: '#16A34A',
        قهوه‌ای: '#92400E',
        طلایی: '#CA8A04',
        نقره‌ای: '#9CA3AF',
        خاکستری: '#6B7280',
        صورتی: '#EC4899',
        شفاف: '#E5E7EB',
    };

    return map[value.value] ?? null;
}
