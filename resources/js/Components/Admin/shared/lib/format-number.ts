const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const ARABIC_DIGITS = '٠١٢٣٤٥٦٧٨٩';

export function normalizeDigits(value: string): string {
    return value
        .replace(/[۰-۹]/g, (char) => {
            const index = PERSIAN_DIGITS.indexOf(char);
            return index >= 0 ? String(index) : char;
        })
        .replace(/[٠-٩]/g, (char) => {
            const index = ARABIC_DIGITS.indexOf(char);
            return index >= 0 ? String(index) : char;
        });
}

type ParseNumberInputOptions = {
    allowDecimal?: boolean;
};

export function parseNumberInput(value: string, options: ParseNumberInputOptions = {}): string {
    const { allowDecimal = false } = options;
    let normalized = normalizeDigits(value).replace(/[,،٬\s]/g, '');

    if (allowDecimal) {
        normalized = normalized.replace(/[^\d.]/g, '');
        const [whole, ...fractionParts] = normalized.split('.');
        if (fractionParts.length === 0) {
            return whole;
        }
        return `${whole}.${fractionParts.join('')}`;
    }

    return normalized.replace(/[^\d]/g, '');
}

type FormatNumberInputOptions = {
    allowDecimal?: boolean;
    maximumFractionDigits?: number;
};

export function formatNumberInput(
    value: string | number | null | undefined,
    options: FormatNumberInputOptions = {},
): string {
    const { allowDecimal = false, maximumFractionDigits = allowDecimal ? 2 : 0 } = options;

    if (value == null || value === '') {
        return '';
    }

    const raw =
        typeof value === 'number'
            ? Number.isFinite(value)
                ? String(value)
                : ''
            : parseNumberInput(value, { allowDecimal });

    if (!raw) {
        return '';
    }

    const num = Number(raw);
    if (!Number.isFinite(num)) {
        return '';
    }

    return num.toLocaleString('fa-IR', {
        useGrouping: true,
        maximumFractionDigits,
        minimumFractionDigits: 0,
    });
}

export function formatYear(value: number | string | null | undefined): string {
    const year = Number(value);
    if (!Number.isFinite(year)) {
        return '—';
    }
    return year.toLocaleString('fa-IR', { useGrouping: false });
}

export function formatPersianDigits(value: string | number | null | undefined): string {
    if (value == null || value === '') {
        return '—';
    }

    return String(value)
        .replace(/[0-9]/g, (digit) => PERSIAN_DIGITS[Number(digit)] ?? digit)
        .replace(/[٠-٩]/g, (char) => {
            const index = ARABIC_DIGITS.indexOf(char);
            return index >= 0 ? PERSIAN_DIGITS[index]! : char;
        });
}
