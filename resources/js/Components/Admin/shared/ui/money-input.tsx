import { AdminInput, type AdminInputProps } from '@/Components/Admin/shared/ui/input';
import { formatNumberInput, parseNumberInput } from '@/Components/Admin/shared/lib/format-number';
import { cn } from '@/lib/utils';

type MoneyInputProps = Omit<AdminInputProps, 'value' | 'onChange' | 'type'> & {
    value: string;
    onChange: (value: string) => void;
    allowDecimal?: boolean;
};

export function MoneyInput({
    value,
    onChange,
    allowDecimal = false,
    className,
    inputMode,
    ...props
}: MoneyInputProps) {
    return (
        <AdminInput
            {...props}
            type="text"
            inputMode={inputMode ?? (allowDecimal ? 'decimal' : 'numeric')}
            dir="ltr"
            className={cn('text-left font-mono', className)}
            value={formatNumberInput(value, { allowDecimal })}
            onChange={(event) => onChange(parseNumberInput(event.target.value, { allowDecimal }))}
        />
    );
}
