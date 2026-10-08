import type { CatalogVariantAttribute } from '@/Components/Admin/Catalog/variant-attributes';
import { swatchColor } from '@/Components/Admin/Catalog/variant-attributes';

export type VariantEditorRow = {
    key: string;
    id?: number;
    name: string;
    code: string | null;
    attributes: Record<string, string>;
    sku: string;
    price: number | '';
    discount_type: '' | 'percentage' | 'fixed';
    discount_value: number | '';
    final_price: number | '';
    sale_start: string;
    sale_end: string;
    stock: number | '';
    min_stock: number;
    display_mode: string;
};

export type VariantBulkState = {
    price: string;
    discount_type: '' | 'percentage' | 'fixed';
    discount_value: string;
    display_mode: string;
    sale_start: string;
    sale_end: string;
    stock: string;
    min_stock: string;
};

export const emptyBulkState = (): VariantBulkState => ({
    price: '',
    discount_type: '',
    discount_value: '',
    display_mode: '',
    sale_start: '',
    sale_end: '',
    stock: '',
    min_stock: '',
});

export function combinationKey(attributes: Record<string, string>): string {
    const entries = Object.entries(attributes)
        .filter(([, value]) => value !== '')
        .sort(([a], [b]) => Number(a) - Number(b));

    if (entries.length === 0) {
        return 'simple';
    }

    return entries.map(([attrId, valueId]) => `${attrId}:${valueId}`).join('|');
}

export function calculateSalePrice(row: VariantEditorRow): VariantEditorRow {
    const price = typeof row.price === 'number' ? row.price : parseFloat(String(row.price));
    const discount = typeof row.discount_value === 'number'
        ? row.discount_value
        : parseFloat(String(row.discount_value));

    if (!price || !discount || !row.discount_type) {
        return { ...row, final_price: '' };
    }

    if (row.discount_type === 'percentage') {
        return { ...row, final_price: Math.round(price - (price * discount) / 100) };
    }

    if (row.discount_type === 'fixed') {
        return { ...row, final_price: Math.max(0, Math.round(price - discount)) };
    }

    return { ...row, final_price: '' };
}

export function withCalculatedPrices(rows: VariantEditorRow[]): VariantEditorRow[] {
    return rows.map((row) => calculateSalePrice(row));
}

export function emptySimpleVariantRow(): VariantEditorRow {
    return calculateSalePrice({
        key: 'simple',
        name: 'کالای ساده',
        code: null,
        attributes: {},
        sku: '',
        price: '',
        discount_type: '',
        discount_value: '',
        final_price: '',
        sale_start: '',
        sale_end: '',
        stock: '',
        min_stock: 0,
        display_mode: 'normal',
    });
}

type ServerVariant = {
    id: number;
    sku: string | null;
    price: number | null;
    final_price: number | null;
    discount_type?: string | null;
    discount_value?: number | null;
    sale_start?: string | null;
    sale_end?: string | null;
    stock: number;
    min_stock?: number;
    display_mode: string;
    attributes?: Record<string, string>;
    attribute_value_ids?: number[];
    attributes_text?: string;
    title?: string | null;
};

export function rowsFromServerVariants(
    variants: ServerVariant[],
    variantAttributes: CatalogVariantAttribute[],
): VariantEditorRow[] {
    if (variants.length === 0) {
        return [emptySimpleVariantRow()];
    }

    const valueMeta = new Map<number, { label: string; code: string | null; attributeId: number }>();

    for (const attr of variantAttributes) {
        for (const value of attr.values) {
            valueMeta.set(value.id, {
                label: value.value,
                code: swatchColor(value),
                attributeId: attr.id,
            });
        }
    }

    return withCalculatedPrices(
        variants.map((variant) => {
            const attributes = { ...(variant.attributes ?? {}) };
            const nameParts: string[] = [];
            let code: string | null = null;

            for (const valueId of variant.attribute_value_ids ?? []) {
                const meta = valueMeta.get(valueId);
                if (!meta) {
                    continue;
                }
                attributes[String(meta.attributeId)] = String(valueId);
                nameParts.push(meta.label);
                if (meta.code) {
                    code = meta.code;
                }
            }

            const name =
                nameParts.length > 0
                    ? nameParts.join(' / ')
                    : variant.attributes_text || variant.title || 'کالای ساده';

            const key = combinationKey(attributes);

            return calculateSalePrice({
                key,
                id: variant.id,
                name,
                code,
                attributes,
                sku: variant.sku ?? '',
                price: variant.price ?? '',
                discount_type: (variant.discount_type as VariantEditorRow['discount_type']) ?? '',
                discount_value: variant.discount_value ?? '',
                final_price: variant.final_price ?? '',
                sale_start: variant.sale_start ?? '',
                sale_end: variant.sale_end ?? '',
                stock: variant.stock ?? '',
                min_stock: variant.min_stock ?? 0,
                display_mode: variant.display_mode || 'normal',
            });
        }),
    );
}

export function generateFromColorSelection(
    variantAttributes: CatalogVariantAttribute[],
    selectedValueIds: number[],
    previousRows: VariantEditorRow[],
): VariantEditorRow[] {
    const groups: Record<string, { id: string; label: string }[]> = {};
    const colors: Record<string, string> = {};

    for (const attribute of variantAttributes) {
        const selected = attribute.values.filter((value) => selectedValueIds.includes(value.id));

        if (selected.length === 0) {
            continue;
        }

        groups[String(attribute.id)] = selected.map((value) => ({
            id: String(value.id),
            label: value.value,
        }));

        if (attribute.slug === 'color') {
            for (const value of selected) {
                const code = swatchColor(value);
                if (code) {
                    colors[String(value.id)] = code;
                }
            }
        }
    }

    const attrKeys = Object.keys(groups).sort();

    if (attrKeys.length === 0) {
        return [emptySimpleVariantRow()];
    }

    const combos = buildCombos(attrKeys.map((key) => groups[key]));
    const oldMap = new Map(previousRows.map((row) => [row.key, row]));

    return withCalculatedPrices(
        combos.map((combo) => {
            const attributes: Record<string, string> = {};
            const nameParts: string[] = [];
            let code: string | null = null;

            combo.forEach((item, index) => {
                const attrKey = attrKeys[index];
                attributes[attrKey] = item.id;
                nameParts.push(item.label);
                if (colors[item.id]) {
                    code = colors[item.id];
                }
            });

            const key = combinationKey(attributes);
            const existing = oldMap.get(key);

            return calculateSalePrice({
                key,
                id: existing?.id,
                name: nameParts.join(' / '),
                code,
                attributes,
                sku: existing?.sku ?? '',
                price: existing?.price ?? '',
                discount_type: existing?.discount_type ?? '',
                discount_value: existing?.discount_value ?? '',
                final_price: '',
                sale_start: existing?.sale_start ?? '',
                sale_end: existing?.sale_end ?? '',
                stock: existing?.stock ?? '',
                min_stock: existing?.min_stock ?? 0,
                display_mode: existing?.display_mode ?? 'normal',
            });
        }),
    );
}

function buildCombos<T>(arrays: T[][]): T[][] {
    return arrays.reduce<T[][]>(
        (acc, curr) => {
            const res: T[][] = [];
            acc.forEach((a) => {
                curr.forEach((b) => {
                    res.push([...a, b]);
                });
            });
            return res;
        },
        [[]],
    );
}

export function applyBulkToRows(rows: VariantEditorRow[], bulk: VariantBulkState): VariantEditorRow[] {
    return withCalculatedPrices(
        rows.map((row) => {
            const next = { ...row };

            if (bulk.display_mode !== '') {
                next.display_mode = bulk.display_mode;
            }
            if (bulk.price !== '') {
                next.price = Number(bulk.price);
            }
            if (bulk.discount_type !== '') {
                next.discount_type = bulk.discount_type;
            }
            if (bulk.discount_value !== '') {
                next.discount_value = Number(bulk.discount_value);
            }
            if (bulk.sale_start !== '') {
                next.sale_start = bulk.sale_start;
            }
            if (bulk.sale_end !== '') {
                next.sale_end = bulk.sale_end;
            }
            if (bulk.stock !== '') {
                next.stock = Number(bulk.stock);
            }
            if (bulk.min_stock !== '') {
                next.min_stock = Number(bulk.min_stock);
            }

            return next;
        }),
    );
}

export function addEmptyVariantRow(
    variantAttributes: CatalogVariantAttribute[],
    rows: VariantEditorRow[],
): VariantEditorRow[] {
    const attributes: Record<string, string> = {};

    for (const attr of variantAttributes) {
        attributes[String(attr.id)] = '';
    }

    const key = `manual-${Date.now()}-${rows.length}`;

    return [
        ...rows,
        calculateSalePrice({
            key,
            name: 'واریانت جدید',
            code: null,
            attributes,
            sku: '',
            price: '',
            discount_type: '',
            discount_value: '',
            final_price: '',
            sale_start: '',
            sale_end: '',
            stock: '',
            min_stock: 0,
            display_mode: 'normal',
        }),
    ];
}

export function duplicateVariantRow(rows: VariantEditorRow[], index: number): VariantEditorRow[] {
    const source = rows[index];
    if (!source) {
        return rows;
    }

    const copy = {
        ...source,
        id: undefined,
        key: `copy-${Date.now()}-${index}`,
        sku: '',
    };

    return [...rows.slice(0, index + 1), copy, ...rows.slice(index + 1)];
}

export function hasDuplicateCombination(rows: VariantEditorRow[], index: number): boolean {
    const current = rows[index];
    if (!current) {
        return false;
    }

    const key = combinationKey(current.attributes);
    if (key === 'simple') {
        return false;
    }

    return rows.some((row, i) => i !== index && combinationKey(row.attributes) === key);
}

export function selectedValueIdsFromRows(rows: VariantEditorRow[]): number[] {
    const ids: number[] = [];

    for (const row of rows) {
        for (const valueId of Object.values(row.attributes)) {
            if (valueId !== '') {
                ids.push(Number(valueId));
            }
        }
    }

    return [...new Set(ids)];
}
