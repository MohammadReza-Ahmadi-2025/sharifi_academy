import type { SelectOption } from '@/Components/Admin/shared/ui/select';

export function mapToSelectOptions(items: { value: string | number; label: string }[]): SelectOption[] {
    return items.map((item) => ({
        value: String(item.value),
        label: item.label,
    }));
}

export function findSelectOption(
    options: SelectOption[],
    value: string | number | null | undefined,
): SelectOption | null {
    if (value === '' || value == null) {
        return null;
    }

    return options.find((option) => option.value === String(value)) ?? null;
}

export function findSelectOptions(
    options: SelectOption[],
    values: (string | number)[],
): SelectOption[] {
    const wanted = new Set(values.map(String));

    return options.filter((option) => wanted.has(option.value));
}
