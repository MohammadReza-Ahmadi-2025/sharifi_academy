import { cn } from '@/lib/utils';

type Props = {
    label: string;
    htmlFor?: string;
    required?: boolean;
    error?: string;
    hint?: string;
    children: React.ReactNode;
    className?: string;
};

export function SiteFormField({
    label,
    htmlFor,
    required,
    error,
    hint,
    children,
    className,
}: Props) {
    return (
        <div className={cn('space-y-1.5', className)}>
            <label htmlFor={htmlFor} className="block text-sm font-medium text-text">
                {label}
                {required ? <span className="mr-1 text-rose-600">*</span> : null}
            </label>
            {children}
            {hint && !error ? <p className="text-xs text-neutral-500">{hint}</p> : null}
            {error ? <p className="text-xs text-rose-600">{error}</p> : null}
        </div>
    );
}
